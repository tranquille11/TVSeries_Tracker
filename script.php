<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PHPHtmlParser\Dom;

$contents = file_get_contents(__DIR__.'/seriale.txt');
$allSeries = preg_split('/\s/', $contents);

$dom = new Dom();
$seriale = [];

foreach ($allSeries as $series) {
    $url = 'http://www.filmeserialeonline.org/seriale/' . $series;
    try {
        $page = $dom->loadFromUrl($url);
    } catch (\PHPHtmlParser\Exceptions\ChildNotFoundException $e) {
    } catch (\PHPHtmlParser\Exceptions\CircularException $e) {
    } catch (\PHPHtmlParser\Exceptions\CurlException $e) {
    } catch (\PHPHtmlParser\Exceptions\StrictException $e) {
    }

    $name = $page->find('h1')[0]->text;
    $seriale[$name]['link'] = $url;
    $nextEpisode = $page->getElementsByClass('nepisod')[0];

    if (empty($nextEpisode)) {
        $status = $page->find('.status');
        $seriale[$name]['next_episode'] = strtoupper($status->firstChild()->text);

    } else {
        $arr = preg_split('/\s/', $nextEpisode->text);
        $date = array_slice($arr, -1, 1);

        $seriale[$name]['next_episode'] = $date[0];
    }

    $seasons = count($page->getElementsByClass('se-c'));
    $seriale[$name]['total_seasons'] = $seasons;

    $episodes = $page->getElementsByClass('episodiotitle');
    $seriale[$name]['no_of_episodes'] = count($episodes);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$reader = new PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$reader->setReadDataOnly(true);
$existingSpreadsheet = $reader->load($_SERVER['HOME'] . '/Documents/personal_stuff/seriale.xlsx');

$counter = 3;
foreach ($seriale as $key => $serial) {

    $hyper = new Hyperlink($serial['link']);
    $sheet->setCellValueByColumnAndRow(2, $counter, $key);
    $sheet->setCellValueByColumnAndRow(3, $counter, $serial['total_seasons']);
    $sheet->setCellValueByColumnAndRow(4, $counter, $serial['no_of_episodes']);
    $sheet->setCellValueByColumnAndRow(5, $counter, $serial['next_episode']);
    $sheet->setCellValueByColumnAndRow(6, $counter, 'Click here');
    $sheet->setHyperlink('F' . $counter, $hyper);

    $existingNE[$key] = $existingSpreadsheet->getActiveSheet()->getCell('E' . $counter)->getValue();
    $thisNE[$key] = $serial['next_episode'];
    $counter++;
}

if (array_diff($existingNE, $thisNE) === []) {
    exit;
}

$sheet->setCellValueByColumnAndRow(2, 2, 'Series Name');
$sheet->setCellValueByColumnAndRow(3, 2, 'Total Seasons');
$sheet->setCellValueByColumnAndRow(4, 2, 'No Of Episodes');
$sheet->setCellValueByColumnAndRow(5, 2, 'Next Episode');
$sheet->setCellValueByColumnAndRow(6, 2, 'Link');



$sheet->setAutoFilter('B2:F' . count($seriale));
$writer = new Xlsx($spreadsheet);


try {
    $writer->save('seriale.xlsx');
} catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
    echo "file cannot be saved";
}

