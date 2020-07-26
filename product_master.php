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

// 5, 終了


class Item
{
	// プログラム起動時に指定フォルダ・ファイルの確認をし、存在しなければ作成する
	public function __construct()
	{
		echo Guide::$format["操作開始"];
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
		echo Guide::$format["操作選択"];
		$num = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($num, ChoiceKey::$format);
		$errorMsg = $inputMatchValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			if ($errorMsg === "unmatch") {
				echo Guide::$format["入力値不明"];
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
			case $num === ChoiceKey::$format["商品一覧表示"]:
				$this->show();
				break;
			case $num === ChoiceKey::$format["商品登録"]:
				$this->add();
				break;
			case $num === ChoiceKey::$format["商品削除"]:
				$this->delete();
				break;
			case $num === ChoiceKey::$format["商品一覧CSV出力"]:
				$this->csv();
				break;
			case $num === ChoiceKey::$format["終了"]:
				$this->quit();
				break;
		}
	}

	// プログラム継続確認
	private function nextChoice()
	{
		$num = $this->inputContinue();

		if ($num === ContinueKey::$format["はい"]) {
			return true;
		} elseif ($num === ContinueKey::$format["いいえ"]) {
			return false;
		}
	}

	private function inputContinue()
	{
		echo Guide::$format["継続確認"];
		$num = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($num, ContinueKey::$format);
		$errorMsg = $inputMatchValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			if ($errorMsg === "unmatch") {
				echo Guide::$format["入力値不明"];
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
		echo Guide::$format["商品入力"];
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
			echo Guide::$format["商品不明"];
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
		echo Guide::$format["商品削除"];
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
				echo Guide::$format["ID不明"];
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
		$data = $file->readFile();

		$newFile = new NewFile($data);

		echo Guide::$format["CSV出力"];
	}

	// プログラムの終了
	private function quit()
	{
		exit(Guide::$format["操作終了"]);
	}
}



// 案内文の出力クラス
class Guide
{
	public static $format = array(
		"操作選択" => "操作に該当する番号を入力してください【1:商品一覧,2:商品登録,3:商品削除,4:CSV出力,5:終了】\n",
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
		"CSV出力" => "CSVファイルを出力しました\n"
	);
}



// マジックナンバー管理（メニュー選択用）
class ChoiceKey
{
	public static $format = array(
		"商品一覧表示" => "1",
		"商品登録" => "2",
		"商品削除" => "3",
		"商品一覧CSV出力" => "4",
		"終了" => "5"
	);
}



// マジックナンバー管理（継続確認用）
class ContinueKey
{
	public static $format = array(
		"はい" => "1",
		"いいえ" => "2"
	);
}



// ファイル操作をまとめるクラス
class File
{
	private $dirPath = "./csv/";
	private $filePath = "./csv/item.csv";

	public function dirCheck()
	{
		if (!file_exists($this->dirPath)) {
			echo Guide::$format["フォルダ作成"];
			mkdir($this->dirPath, 0777, true);
		}
	}

	public function fileCheck()
	{
		if (!file_exists($this->filePath)) {
			echo Guide::$format["ファイル作成"];
			$fp = fopen($this->filePath, 'w');
			$line = "id,name,code\n";
			fwrite($fp, $line);
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
}



// CSV出力クラス
class NewFile
{
	private $fileNewPath;
	private $data;

	public function __construct($data)
	{
		$date = date("YmdHis");
		$this->fileNewPath = "./csv/item_list_{$date}.csv";

		$this->data = $data;

		$this->writeNewFile();
	}

	private function writeNewFile()
	{
		$fp = fopen($this->fileNewPath, 'w');
		foreach ($this->data as $line) {
			fputcsv($fp, $line);
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
			$this->errorMsg = Guide::$format["値未入力"];
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
			$this->errorMsg = Guide::$format["値未入力"];
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



$item = new Item;
$item->main();