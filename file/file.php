<?php
// ファイル操作をまとめるクラス
class File
{
	private $dirPath = "./csv/";
	private $filePath = "./csv/item.csv";

	private const HEAD = "id,name,code\n";// 見出しの文字列を定数化
	private const IMPORTPATH = "./csv/import/";
	private const IMPORTCSVPATH = "./csv/import/*.csv";

	public function dirCheck()
	{
		if (!file_exists($this->dirPath)) {
			echo Item::FORMAT["フォルダ作成"];
			mkdir($this->dirPath, 0777, true);
		}
	}

	public function fileCheck()
	{
		if (!file_exists($this->filePath)) {
			echo Item::FORMAT["ファイル作成"];
			$fp = fopen($this->filePath, 'w');
			fwrite($fp, self::HEAD);
			fclose($fp);
		}
	}

	public function readFile()
	{
		$fp = fopen($this->filePath, 'r');
		while ($line = fgetcsv($fp)) {
			$data[] = $line;
		}
		fclose($fp);
		return $data;
	}

	public function addFile($line)
	{
		$fp = fopen($this->filePath, 'a+');
		fwrite($fp, $line);
		fclose($fp);
	}

	public function writeFile($data)
	{
		$fp = fopen($this->filePath, 'w');
		foreach ($data as $line) {
			fputcsv($fp, $line);
		}
		fclose($fp);
	}

	public function newCsv()
	{
		$data = $this->readFile();

		$date = date("YmdHis");
		$fileNewPath = "./csv/item_list_{$date}.csv";

		$fp = fopen($fileNewPath, 'w');
		foreach ($data as $line) {
			fputcsv($fp, $line);
		}
		fclose($fp);
	}

	public function import()
	{
		$importPath = self::IMPORTPATH;
		if (!file_exists($importPath)) {
			echo Item::FORMAT["読込用フォルダ作成"];
			mkdir($importPath, 0777, true);
			return;
		}

		foreach (glob(self::IMPORTCSVPATH) as $csvPath) {
			$csvFiles[] = $csvPath;
		}

		if (empty($csvFiles)) {
			echo Item::FORMAT["読込用ファイル不明"];
			return;
		}

		$fp = fopen($this->filePath, 'a+');
		foreach ($csvFiles as $csvPath) {
			$csvFp = fopen($csvPath, 'r');

			$data = [];
			while ($line = fgetcsv($csvFp)) {
				$data[] = $line;
			}

			$importCsvValidation = new ImportCsvValidation($data);
			$errorMsg = $importCsvValidation->getErrorMsg();
			if (!empty($errorMsg)) {
				echo "【読込エラー】$csvPath\n";
				echo $errorMsg;
				continue;
			}
			
			foreach ($data as $importLine) {
				fputcsv($fp, $importLine);
			}
			fclose($csvFp);
		}
		fclose($fp);
	}
}