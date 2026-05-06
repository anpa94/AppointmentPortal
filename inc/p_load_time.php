<?php

include 'config.php';
include 'func.php';
require_once __DIR__ . '/App/Twig.php';

$twig = new Twig(__DIR__ . '/../templates');

if (strlen($_POST['search']) < 2) {
    echo $twig->render('p_load_time.twig', [
        'message' => 'Das Zeitfenster muss mindestens 10 Minuten betragen.',
        'save_button' => '',
        'day_range' => '',
        'slots_table' => ''
    ]);
    exit;
}

$row = $db_link->query('SELECT starttime, endtime FROM projects WHERE id = "'.$_POST['p'].'"')->fetch_object();
$starttimeTemp = strtotime($row->starttime);
$duration = intval($_POST['search']);
$endtimeTemp = strtotime('+'.$duration.' minutes', $starttimeTemp);

$table = '<table class="table table-hover-sm"><thead><tr><th>ID</th><th>Start</th><th>Ende</th></thead><tbody>';
$i = 1;
while ($endtimeTemp <= strtotime($row->endtime)) {
    $table .= '<tr><td>'.$i.'</td><td>'.date('H:i:s', $starttimeTemp).' Uhr</td><td>'.date('H:i:s', $endtimeTemp).' Uhr</td></tr>';
    $starttimeTemp = $endtimeTemp;
    $endtimeTemp = strtotime('+'.$duration.' minutes', $starttimeTemp);
    $i++;
}
$table .= '</tbody></table>';

echo $twig->render('p_load_time.twig', [
    'message' => '',
    'save_button' => '<button class="btn btn-success addtimeslots" id="'.$_POST['p'].'" time="'.$_POST['search'].'"><i class="fa fa-bullhorn" aria-hidden="true"></i> Terminblöcke speichern</button><br>(alle zuvor definierten Terminblöcke werden gelöscht)</br>',
    'day_range' => '<div class="well well-sm">Tagesbeginn <i class="fa fa-hand-o-right" aria-hidden="true"><b></i> '.$row->starttime.' Uhr </b><i class="fa fa-chevron-left" aria-hidden="true"></i><i class="fa fa-chevron-right" aria-hidden="true"></i> Tagesende <i class="fa fa-hand-o-right" aria-hidden="true"></i><b> '.$row->endtime.' Uhr</b></div>',
    'slots_table' => $table
]);
