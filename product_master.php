<?php
// ★ 商品マスター登録プログラム ★

// メインメニュー
// 1, 商品一覧表示
// 2, 商品登録
// 3, 商品削除
// 4, 商品CSV出力
// 5, 終了

// 1, 商品一覧表示
// 【表示項目】
// ・id
// ・商品名
// ・JANコード

// 2, 商品登録
// ・id生成（自動）
// ・商品名 - 入力

// ・JANコード生成(自動)
// ※JANコード生成ルールは、9桁のランダムな数字 + ID３桁
// ex) id = 1 のアイテムなら
// 9桁のランダムな数字 + 001

// 3, 商品削除
// id番号を入力し、対象の商品を一覧から削除する

// 4, 商品CSV出力
// 現在登録されている商品一覧をCSVで出力する
// パス：：　./csv/item_list_{現在時刻YmdHis}.csv
// 【出力項目】　
// ・id
// ・商品名
// ・JANコード

// 5, CSV読込
// import/というディレクトリを作成し、その中にimport用のcsvファイルを要設置
// もしcsvが１つ以上あったら、importディレクトリ内のファイルをすべて読み込み
// csvの内容を上書き登録

// 6, 終了


class Item
{
	// プログラム起動時に指定フォルダ・ファイルの確認をし、存在しなければ作成する
	public function __construct()
	{
		echo Config::$guide["操作開始"];
		$file = new File;
		$file->dirCheck();
		$file->fileCheck();
	}

	// 実行プログラム
	public function main()
	{
		$num = $this->inputChoice();
		$this->choice($num);

		$isContinue = $this->nextChoice();
		if ($isContinue) {
			return $this->main();
		} else {
			$this->quit();
		}
	}

	// メニュー入力
	private function inputChoice()
	{
		echo Config::$guide["操作選択"];
		$num = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($num, Config::$choiceKey);
		$errorMsg = $inputMatchValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			if ($errorMsg === "unmatch") {
				echo Config::$guide["入力値不明"];
			} else {
				echo $errorMsg;
			}
			return $this->inputChoice();
		}
		return $num;
	}

	// 各機能への振り分け処理
	private function choice($num)
	{
		switch (true) {
			case $num === Config::$choiceKey["商品一覧表示"]:
				$this->show();
				break;
			case $num === Config::$choiceKey["商品登録"]:
				$this->add();
				break;
			case $num === Config::$choiceKey["商品削除"]:
				$this->delete();
				break;
			case $num === Config::$choiceKey["商品一覧CSV出力"]:
				$this->csv();
				break;
			case $num === Config::$choiceKey["CSV読込"]:// CSVファイルのインポート機能を追加★
				$this->importCsv();
				break;
			case $num === Config::$choiceKey["終了"]:
				$this->quit();
				break;
		}
	}

	// プログラム継続確認
	private function nextChoice()
	{
		$num = $this->inputContinue();

		if ($num === Config::$continueKey["はい"]) {
			return true;
		} elseif ($num === Config::$continueKey["いいえ"]) {
			return false;
		}
	}

	private function inputContinue()
	{
		echo Config::$guide["継続確認"];
		$num = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($num, Config::$continueKey);
		$errorMsg = $inputMatchValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			if ($errorMsg === "unmatch") {
				echo Config::$guide["入力値不明"];
			} else {
				echo $errorMsg;
			}
			return $this->inputContinue($num);
		}
		return $num;
	}

	// 商品一覧の表示
	private function show()
	{
		$file = new File;
		$data = $file->readFile();

		foreach ($data as $line) {
			echo implode(",", $line)."\n";
		}
	}

	// 商品の登録
	private function add()
	{
		$file = new File;
		$data = $file->readFile();
		
		// id番号の生成
		$dataCount = count($data);
		if ($dataCount === 1) {
			$lastIdAdd = 1;
		} else {
			$lastId = $data[$dataCount-1][0];
			$lastIdAdd = $lastId + 1;
		}
		
		// 商品名の取得
		$name = $this->inputName();
		
		// JANコードの生成
		$randNums = mt_rand(100000000, 999999999);
		$code = sprintf("%d%03d", $randNums, $lastIdAdd);
		
		$line = sprintf("%d,%s,%d\n", $lastIdAdd, $name, $code);
		$file->addFile($line);

		$this->show();
	}


	// 商品名入力
	private function inputName()
	{
		echo Config::$guide["商品入力"];
		$name = trim(fgets(STDIN));

		$inputNameValidation = new InputNameValidation($name);
		$errorMsg = $inputNameValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			echo $errorMsg;
			return $this->inputName();
		}
		return $name;
	}

	// 商品の削除
	private function delete()
	{
		$file = new File;
		$data = $file->readFile();
		
		$countData = count($data);
		if ($countData === 1) {
			echo Config::$guide["商品不明"];
			$this->main();
		}
		
		$id = $this->inputId($data);
		
		foreach ($data as $key => $line) {
			if ($line[0] === $id) {
				$checkKey = $key;
				break;
			}
		}

		unset($data[$checkKey]);

		$file->writeFile($data);

		$this->show();
	}

	// 商品IDの入力
	private function inputId($data)
	{
		echo Config::$guide["商品削除"];
		$id = trim(fgets(STDIN));

		foreach ($data as $key => $line) {
			if ($key > 0) {
				$idList[] = $line[0];
			}
		}

		$inputMatchValidation = new InputMatchValidation($id, $idList);
		$errorMsg = $inputMatchValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			if ($errorMsg === "unmatch") {
				echo Config::$guide["ID不明"];
			} else {
				echo $errorMsg;
			}
			return $this->inputId($data);
		}
		return $id;
	}

	// CSV出力処理
	private function csv()
	{
		$file = new File;
		$file->newCsv();

		echo Config::$guide["CSV出力"];
	}

	// CSV読込処理
	private function importCsv()
	{
		$file = new File;
		$file->import();
	}

	// プログラムの終了
	private function quit()
	{
		exit(Config::$guide["操作終了"]);
	}
}


class Config// 出力文やマジックナンバーを管理するため、Configクラスを生成　修正★
{
	public static $guide = array(
		"操作選択" => "操作に該当する番号を入力してください【1:商品一覧,2:商品登録,3:商品削除,4:CSV出力,5:CSV読込,6:終了】\n",
		"操作終了" => "プログラムを終了します\n",
		"継続確認" => "操作を続けますか？【1:はい,2:いいえ】\n",
		"商品入力" => "商品名を入力してください\n",
		"商品削除" => "削除をする商品IDを入力してください\n",
		"操作開始" => "プログラムを開始します\n",
		"フォルダ作成" => "csvフォルダが存在しないため作成します\n",
		"ファイル作成" => "item.csvファイルが存在しないため作成します\n",
		"値未入力" => "値を入力してください\n",
		"ID不明" => "存在するIDを入力してください\n",
		"入力値不明" => "該当の番号を入力してください\n",
		"商品不明" => "商品が登録されていません\n",
		"CSV出力" => "CSVファイルを出力しました\n",
		"読込用フォルダ作成" => "読込用のimportフォルダがないため作成します\n",
		"読込用データ不明" => "データが入っていないため読込めませんでした\n",
		"読込用ファイル不明" => "ファイルが存在しないため読み込めませんでした\nimportフォルダに読込用ファイルを格納してください\n"
	);

	public static $choiceKey = array(
		"商品一覧表示" => "1",
		"商品登録" => "2",
		"商品削除" => "3",
		"商品一覧CSV出力" => "4",
		"CSV読込" => "5",
		"終了" => "6"
	);

	public static $continueKey = array(
		"はい" => "1",
		"いいえ" => "2"
	);
}


// ファイル操作をまとめるクラス
class File
{
	private $dirPath = "./csv/";
	private $filePath = "./csv/item.csv";
	private const HEAD = "id,name,code\n";// 見出しの文字列を定数化　修正★
	private const IMPORTPATH = "./csv/import/";// 追加★
	private const IMPORTCSVPATH = "./csv/import/*.csv";// 追加★

	public function dirCheck()
	{
		if (!file_exists($this->dirPath)) {
			echo Config::$guide["フォルダ作成"];
			mkdir($this->dirPath, 0777, true);
		}
	}

	public function fileCheck()
	{
		if (!file_exists($this->filePath)) {
			echo Config::$guide["ファイル作成"];
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

	public function newCsv()//CSV出力クラスを削除し、メソッドをFileクラスに含める形とした　修正★
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

	public function import()// CSVファイルのインポート機能を追加★
	{
		$importPath = self::IMPORTPATH;
		if (!file_exists($importPath)) {
			echo Config::$guide["読込用フォルダ作成"];
			mkdir($importPath, 0777, true);
			return;
		}

		foreach (glob(self::IMPORTCSVPATH) as $csvPath) {
			$csvFiles[] = $csvPath;
		}

		if (empty($csvFiles)) {
			echo Config::$guide["読込用ファイル不明"];
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


// バリデーション
class InputNameValidation
{
	private $data;
	private $errorMsg;

	public function __construct($data)
	{
		$this->data = $data;
		$this->validation();
	}

	private function validation()
	{
		if (empty($this->data)) {
			$this->errorMsg = Config::$guide["値未入力"];
			return;
		}
	}

	public function getErrorMsg()
	{
		if ($this->errorMsg) {
			return $this->errorMsg;
		}
	}
}



// 入力値が指定されている場合のバリデーション
class InputMatchValidation
{
	private $num;
	private $match;
	private $errorMsg;

	public function __construct($num, $match)
	{
		$this->num = $num;
		$this->match = $match;
		$this->validation();
	}

	private function validation()
	{
		if (empty($this->num)) {
			$this->errorMsg = Config::$guide["値未入力"];
			return;
		}
		if (!in_array($this->num, $this->match, true)) {
			$this->errorMsg = "unmatch";
			return;
		}
	}

	public function getErrorMsg()
	{
		if ($this->errorMsg) {
			return $this->errorMsg;
		}
	}
}


class ImportCsvValidation// 追加★
{
	private $data;
	private $errorMsg;

	public function __construct($data)
	{
		$this->data = $data;
		$this->validation();
	}

	private function validation()
	{
		if (empty($this->data)) {
			$this->errorMsg = Config::$guide["読込用データ不明"];
			return;
		}
	}

	public function getErrorMsg()
	{
		if ($this->errorMsg) {
			return $this->errorMsg;
		}
	}
}



$item = new Item;
$item->main();