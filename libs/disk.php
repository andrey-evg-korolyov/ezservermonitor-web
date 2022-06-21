<?php
//require '/../autoload.php';
require __DIR__.'/../autoload.php';
$Config = new Config();

$datas = array();

# if there are more than 7 awk's colums it means the mount point name contains spaces
# so consider the first colums as a unique colum and the last 6 as real colums
if (!(exec('/bin/df -T -P | tail -n +2 | awk \'{ if (NF > 7) { for (i=1; i<NF-6; i++) { printf("%s ", $i); } for (i=NF-6; i<NF; i++) { printf("%s,", $i); } print $NF; } else { print $1","$2","$3","$4","$5","$6","$7; } }\'', $df)))
{
    $datas[] = array(
        'total'         => 'N.A',
        'used'          => 'N.A',
        'free'          => 'N.A',
        'percent_used'  => 0,
        'mount'         => 'N.A',
        'filesystem'    => 'N.A',
    );
}
else
{
    $mounted_points = array();
    $key = 0;

    foreach ($df as $mounted)
    {
        list($filesystem, $type, $total, $used, $free, $percent, $mount) = explode(',', $mounted);

        if ($percent > 100)
            $percent = 100;

        if (strpos($type, 'tmpfs') !== false && $Config->get('disk:show_tmpfs') === false)
            continue;

        if (strpos($filesystem, '/dev/loop') !== false && $Config->get('disk:show_loop') === false)
            continue;

        foreach ($Config->get('disk:ignore_mounts') as $to_ignore)
        {
            if ($mount === $to_ignore)
                continue 2;
        }

        if (!in_array($mount, $mounted_points))
        {
            $mounted_points[] = trim($mount);

            $datas[$key] = array(
                'total'         => Misc::getSize($total * 1024),
                'used'          => Misc::getSize($used * 1024),
                'free'          => Misc::getSize($free * 1024),
                'percent_used'  => trim($percent, '%'),
                'mount'         => $mount,
            );

            if ($Config->get('disk:show_filesystem'))
                $datas[$key]['filesystem'] = $filesystem;
        }

        $key++;
        
        
//            //тестовое сохранение в БД//
//        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);       
//        $mysqli = new mysqli('localhost', 'andrey', 'andrey1984k13', 'ezmon');
//        $result = $mysqli->query("INSERT INTO disk (filesystem, used, total) VALUES('$filesystem', '$used', '$total')");
    
    include_once __DIR__.'/Persist/disk/Disk_DAO_Factory.php';
    include_once __DIR__.'/Persist/disk/dao/Disk_DAO_Interface.php';
    include_once __DIR__.'/Persist/disk/dao/Disk_DAO_DB.php';
    include_once __DIR__.'/Persist/disk/dto/Disk_DTO.php';
    
    ini_set('display_errors', true);
    error_reporting(E_ALL & ~E_NOTICE);
    $factory = Disk_DAO_Factory::getFactory();
    $dao = $factory->getDbDao();
    
    $data = new Disk_DTO($filesystem, $used, $total);
    $dao->InsertData($data);

}

 }
   
echo json_encode($datas);
