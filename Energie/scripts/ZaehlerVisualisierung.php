<?php
/*
    Zähler-Dashboard
    - Archiviert automatisch Zählerstände
    Programmierer: Mike Dorr
    Projekt: HVG241 Meisterprüfung
*/

$catName = "Messkonzept_Skalierung_Historie";
$htmlName = "Zähler_Dashboard";

// Kategorie prüfen
$catID = @IPS_GetObjectIDByName($catName, 0);
if ($catID === false) {
    echo "Kategorie '$catName' nicht gefunden!\n";
    return;
}

// Zähler-Variablen finden (beginnt mit "Z")
$vars = [];
foreach (IPS_GetChildrenIDs($catID) as $cid) {
    $obj = IPS_GetObject($cid);
    if ($obj['ObjectType'] == 2) {
        $name = $obj['ObjectName'];
        if (preg_match('/^Z(\d+)_/', $name, $match)) {
            $index = (int)$match[1];
            $vars[$index] = ['name' => $name, 'id' => $cid];
        }
    }
}

if (empty($vars)) {
    echo "Keine Zähler-Variablen gefunden! (Erwarte Namen wie 'Z1_', 'Z2_', ...)\n";
    return;
}

// Nach Zählernummer sortieren
ksort($vars);

// Logging aktivieren
$archiveID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];
foreach ($vars as $entry) {
    AC_SetLoggingStatus($archiveID, $entry['id'], true);
    AC_SetAggregationType($archiveID, $entry['id'], 1);
}
IPS_ApplyChanges($archiveID);

// HTML-Variable für Dashboard erstellen
$htmlID = @IPS_GetVariableIDByName($htmlName, $catID);
if ($htmlID === false) {
    $htmlID = IPS_CreateVariable(3);
    IPS_SetParent($htmlID, $catID);
    IPS_SetName($htmlID, $htmlName);
    IPS_SetVariableCustomProfile($htmlID, "~HTMLBox");
}

// HTML-Dashboard erzeugen
$html  = '<div style="font-family:Segoe UI, sans-serif; padding:10px;">';
$html .= '<h2 style="margin-bottom:10px;">Zähler-Dashboard (numerisch sortiert)</h2>';
$html .= '<table style="width:100%; border-collapse:collapse;">';
$html .= '<tr><th align="left">Name</th><th align="right">Wert</th></tr>';

foreach ($vars as $entry) {
    $name = $entry['name'];
    $vid  = $entry['id'];
    $value = GetValueFormatted($vid);
    $html .= "<tr><td>$name</td><td align='right'><b>$value</b></td></tr>";
}

$html .= '</table>';
$html .= '<div style="margin-top:10px; font-size:11px; color:gray;">Stand: '.date("d.m.Y H:i:s").'</div>';
$html .= '</div>';

SetValueString($htmlID, $html);

echo "Dashboard aktualisiert (numerisch sortiert).\n";
?>
