<?php 

namespace Tritility\MatthewCSVTest;

use Tritility\MatthewCSVTest\Imports\ClientsImport;
use App\Http\Controllers\Controller;

class MatthewCSVTestController extends Controller {

  public function __construct() {}

  /**
  * Requests Instagram pics.
  *
  * @return Response
  */
  public function convertSpreadSheetContent() { 
    $path = base_path('public') . "\\spreadsheets\\";
    $spreadsheets = glob($path . "*.xls"); // Find all xls files in directory
    if ( is_array($spreadsheets) && count($spreadsheets) > 0 ) {
      foreach ( $spreadsheets as $x=>$v ) {  // loop through each found xls file
        $csvArray = $columnRules = $header = [];
        $data = $this->getSpreadsheetData($v); // extract data from xls file
        if ( count($data) > 0 ) {
          foreach ( $data as $rownum=>$row ) { // loop through each row of the xls file
            if ( count($row) > 0 ) {
              $csvArrayRow = [];
              foreach ( $row as $cellnum=>$cell ) {
                // check if the row is the header. Check which column each header is, record which column number matches which appropriate data type to be applied to the column.
                if ( $rownum == 2 ) { 
                  $x = $this->createColumnRule($cell);
                  if ( $x != "" ) {
                    $columnRules[$cellnum] = $x;
                    $header[] = $this->convertHeaderRow($cell); // build header row
                  }
                } 
                if ( $rownum > 2 ) {
                  if ( array_key_exists($cellnum, $columnRules) ) { $csvArrayRow[] = $this->sanitizeData($columnRules, $cellnum, $cell); }
                }
              }
              if ( count($csvArrayRow) > 0 ) { 
                if ( $csvArrayRow[5] == '' ) { // fill county column with town value if county is empty  
                  $csvArrayRow[5] = $csvArrayRow[4]; 
                  $csvArrayRow[4] = "";
                }
                if ( $csvArrayRow[4] == '' ) { // fill town column with address 2 value if town is empty 
                  if ( 1 !== preg_match('~[0-9]~', $csvArrayRow[3]) && !strpos(strtolower($csvArrayRow[3]),"road") && !strpos(strtolower($csvArrayRow[3]),"street") ) {
                    $csvArrayRow[4] = $csvArrayRow[3]; 
                    $csvArrayRow[3] = "";
                  }
                }
                if ( $csvArrayRow[0] != "" && $csvArrayRow[1] != null ) {
                  $csvArray[] = $csvArrayRow; 
                }
              }
            }
          }
          $filename = pathinfo($v, PATHINFO_FILENAME);
          $csvResult = $this->writeCSV($csvArray, $path, $filename, $header); // write csv file with sanitized data
        }
      }
    }
    return true;
  }

  /* 
  Get the spreadsheet data
  */
  private function getSpreadsheetData($path) {
    $data = (file_exists($path)) ? \PhpOffice\PhpSpreadsheet\IOFactory::load($path) : []; // load spreadsheet
    $ouput = $data->getActiveSheet()->toArray(null,true,false,true); 
    return $ouput;
  }

  /*
  Write data to CSV
  */
  public function writeCSV($array, $path, $filename, $header) {
    if ( count($array) > 0 && $path != '' && $filename != '' && count($header) > 0 ) {
      if ( !file_exists($path."output/") ) { mkdir($path."output", 0777, true); }
      $fp = fopen($path."output/".$filename."-".strtotime(date("Y-m-d h:i:s")).".csv", "w");
      fputcsv($fp, $header); // write field header
      foreach ($array as $line) {
          fputcsv($fp, $line); // write the remaining data
      }
      fclose($fp);
      return true;
    } else {
      return false;
    }
  }

  private function convertHeaderRow($cell) {
    $check = array('[Company Name]','[Company Number]','[Line of address 1]','[Line of address 2]','[Line of address 3]','[Line of address 4]','[Company Postcode]','[Telephone Number]','[SIC Code and Description]','[Director Forname]','[Director Surname]');
    $replace = array('company_name','company_number','address_line_1','address_line_2','town','county','postcode','telephone','sic_code','contact_first_name','contact_last_name');
    $cell = str_replace($check,$replace,$cell);
    return $cell;
  }

  private function createColumnRule($cell) {
    switch ($cell) {
      case '[Company Name]':
        $ret = 'strlower';
        break;
      case '[Company Number]':
        $ret = 'str';
        break;
      case '[Line of address 1]':
      case '[Line of address 2]':
      case '[Line of address 3]':
      case '[Line of address 4]':
      case '[Director Forname]':
      case '[Director Surname]':
        $ret = 'strupperwords';
        break;
      case '[Telephone Number]':
      case '[SIC Code and Description]':
        $ret = 'num';
        break;
      case '[Company Postcode]':
        $ret = 'strupper';
        break;
      default:
        $ret = '';
        break;
    }
    return $ret;
  }

  public function sanitizeData($rules, $col, $data) {
    $rule = (isset($rules[$col])) ? $rules[$col] : "str";
    switch ($rule) {
      case "strlower": 
        $ret = strtolower($data);
        break;
      case "strupperwords":
        $ret = ucwords($data);
      case "strupper":
        $ret = strtoupper($data);
        break;
      case "num":
        $ret = intval($data);
        break;
      default:
        $ret = $data;
        break;
    }
    return $ret;
  }
}