<?php
header('Content-Type: text/html; charset=utf-8'); 
include 'db.php';

class Importer{
    private $file;

    function __construct($file)
    {
        $this->file = $file;
    }
    
    public function getCSV(string $separator){
        $handle = $this->file;
        set_time_limit(0);

        $array = array();
        $headers = fgetcsv($handle, 0, $separator);
        while (($line = fgetcsv($handle, 0, $separator)) !== FALSE) { 
            $array[] = array_combine($headers, $line);
        }
        fclose($handle);
        return $array; 
    }
    public function setCSV(Array $csv, $separator){
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="export.csv";');
        
        $handle = fopen("php://output", "a"); 
        fputcsv($handle, ['Код', 'Название', 'Error'],$separator);
        foreach ($csv as $value) {
            fputcsv($handle, mb_convert_encoding($value, 'utf-8', 'auto'), "$separator"); 
        }
    
        fclose($handle);
        
    }
}
$importer = new Importer(fopen($_FILES['csvUpload']['tmp_name'], "r"));

$array = $importer->getCSV($conf['separator']);
$validatedArray = [];

foreach ($array as &$row){
    $invalid = preg_replace("~([A-Za-z\p{Cyrillic}0-9\-\.]+)~u", "", $row["Название"]);
    if($invalid !== ""){
        $row["Error"] = sprintf("Недопустимый символ \"%s\" в поле Название", $invalid);
    }else{
        $validatedArray[] = $row;
    }
}
$values = array();
foreach ($validatedArray as $rowValues) {
    foreach ($rowValues as $key => $rowValue) {
         $rowValues[$key] = mysqli_real_escape_string($connection, $rowValues[$key]);
        }

    $values[] = "(" . implode(', ', $rowValues) . ")";
}


$sql = sprintf("INSERT INTO test VALUES %s ON DUPLICATE KEY UPDATE", implode(', ',$values));


if ($connection->query($sql) === TRUE) {
    echo "Everything is going according plan";
  } else {
    echo "Error: " . $sql . "<br>" . $connection->error;
  }

$connection->close();


$importer->setCSV($array, $conf['separator']);

?>