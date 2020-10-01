<?php
define('IN_MYBB', 1);
//define('NO_ONLINE', 1); // Wenn Seite nicht in Wer ist online-Liste auftauchen soll

require_once './global.php';

global $db, $mybb, $templates, $page, $filter_blood, $work ;

add_breadcrumb('Charakterübsicht');


$sort = "username";
$sortway = "ASC";
if(isset($_POST['chara_sort'])) {
    $sort = $_POST['sort_chara'];
    $sortway = $_POST['sort_way'];
}


$blood_query = $db->query("SELECT DISTINCT fid34
        from ".TABLE_PREFIX."userfields
        WHERE fid34 != 'keine Angabe'
        AND fid34 != ''
        ");

while($blood = $db->fetch_array($blood_query)){

    $filter_blood .= "<option value='{$blood['fid34']}'>{$blood['fid34']}</option>";
}

$blood_filter = "%";
$sex_filter = "%";
$gender_filter = "%";
if(isset($_POST['chara_filter'])) {
$blood_filter = $_POST['filter_blood'];
    $sex_filter = $_POST['filter_sex'];
    $gender_filter = $_POST['filter_gender'];
}

$chara_count = 0;

$select_charas = $db->query("SELECT *
FROM ".TABLE_PREFIX."users u
LEFT JOIN ".TABLE_PREFIX."userfields uf
on (u.uid = uf.ufid)
WHERE u.usergroup != '4'
AND NOT u.usergroup = '26'
and not u.usergroup = '28'
and not u.additionalgroups LIKE '54'
and fid34 like '$blood_filter'
and fid55 like '$sex_filter%'
and fid3 like '$gender_filter'
ORDER BY $sort $sortway
");

while($chara = $db->fetch_array($select_charas)){
    $blood = "";
    $height = "";
    $eyes = "";
    $attitude  = "";
    $work = "";
    $age = "";
    $gender = "";
    $sex = "";
    $relation = "";
    $chara_count++;

    $username = format_name($chara['username'], $chara['usergroup'], $chara['displaygroup']);
    $charaname = build_profile_link($username, $chara['uid']);

    if ($chara['birthday']) {
        $age = intval(date('Y', strtotime("1." . $mybb->settings['minica_month'] . "." . $mybb->settings['minica_year'] . "") - strtotime($chara['birthday']))) - 1970;
    } else {
        $age = "k/A";
    }

    $blood = $chara['fid34'];
    $height = $chara['fid56']."m";
    $eyes = $chara['fid57'];
    $attitude = $chara['fid58'];#
    $sex = $chara['fid55'];
    $relation = $chara['fid52'];

    $gender = $chara['fid3'];

    if($chara['fid5'] != '' ){
        $work = "{$chara['fid5']} <br />{$chara['fid6']}";
    }
    if(!empty($chara['job'])){
        $job_sect = $db->simple_select("jobs", "name", "jid = '$chara[jid]'");
        $job_name = $db->fetch_array($job_sect);
        $work = "{$chara['job']} <br />{$job_name['name']}";
    } elseif(!empty($chara['position'])){
        $uni_sect = $db->simple_select("university", "fach", "unid = '$chara[unid]'");
        $uni_fach = $db->fetch_array($uni_sect);
        $work = "{$chara['position']} <br />{$uni_fach['fach']}";
    }elseif(!empty($chara['fid19'])) {
        if ($chara['fid19'] != 'keine Angabe') {
            if(!empty($chara['fid18'])){
                $chara['fid18'] = "für {$chara['fid18']}";
            }
            $work = "{$chara['fid19']} {$chara['fid18']}";
        }
    }


    eval("\$characters_bit .= \"".$templates->get("characters_bit")."\";");

}

eval('$page = "'.$templates->get('characters').'";'); // Hier wird das erstellte Template geladen
output_page($page);