<?php
require dirname(__FILE__) . "/file/file.php";
require dirname(__FILE__) . "/validation/validation.php";

class Item
{
	public const FORMAT = array(
		"操作選択" => "操作に該当する番号を入力してください【1:商品一覧,2:商品登録,3:商品削除,4:CSV出力,5:CSV読込,6:終了】\n",
		"操作終了" => "プログラムを終了します\n",
		"終了確認" => "本当にプログラムを終了してよろしいですか【1:はい,2:いいえ】\n",
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

	private const CHOICE = array(
		"商品一覧表示" => "1",
		"商品登録" => "2",
		"商品削除" => "3",
		"商品一覧CSV出力" => "4",
		"CSV読込" => "5",
		"終了" => "6"
	);

	private const CONTINUE = array(
		"はい" => "1",
		"いいえ" => "2"
	);

	// プログラム起動時に指定フォルダ・ファイルの確認をし、存在しなければ作成する
	public function __construct()
	{
		echo self::FORMAT["操作開始"];
		$file = new File;
		$file->dirCheck();
		$file->fileCheck();
	}

	// 実行プログラム
	public function main()
	{
		$inputData = $this->inputChoice();
		$this->choice($inputData);
	}

	// メニュー入力
	private function inputChoice()
	{
		echo self::FORMAT["操作選択"];
		$inputData = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($inputData, self::CHOICE, self::FORMAT["入力値不明"]);
		$errorMsg = $inputMatchValidation->getErrorMsg();

		if (!empty($errorMsg)) {
			echo $errorMsg;
			return $this->inputChoice();
		}
		return $inputData;
	}

	// 各機能への振り分け処理
	private function choice($inputData)
	{
		switch ($inputData) {
			case self::CHOICE["商品一覧表示"]:
				$this->show();
				break;
			case self::CHOICE["商品登録"]:
				$this->add();
				break;
			case self::CHOICE["商品削除"]:
				$this->delete();
				break;
			case self::CHOICE["商品一覧CSV出力"]:
				$this->csv();
				break;
			case self::CHOICE["CSV読込"]:
				$this->importCsv();
				break;
			case self::CHOICE["終了"]:
				$this->quit();
				break;
		}
	}

	// プログラム継続確認
	private function confirm()
	{
		$inputData = $this->inputConfirm();

		if ($inputData === self::CONTINUE["はい"]) return true;
		if ($inputData === self::CONTINUE["いいえ"]) return false;
	}

	private function inputConfirm()
	{
		echo self::FORMAT["終了確認"];
		$inputData = trim(fgets(STDIN));

		$inputMatchValidation = new InputMatchValidation($inputData, self::CONTINUE, self::FORMAT["入力値不明"]);
		$errorMsg = $inputMatchValidation->getErrorMsg();

		if (!empty($errorMsg)) {
			echo $errorMsg;
			return $this->inputConfirm();
		}
		return $inputData;
	}

	// 商品一覧の表示
	private function show()
	{
		$file = new File;
		$data = $file->readFile();

		foreach ($data as $line) {
			echo implode(",", $line)."\n";
		}

		$this->main();
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
		$inputData = $this->inputName();
		
		// JANコードの生成
		$randNums = mt_rand(100000000, 999999999);
		$code = sprintf("%d%03d", $randNums, $lastIdAdd);
		
		$line = sprintf("%d,%s,%d\n", $lastIdAdd, $inputData, $code);
		$file->addFile($line);

		$this->show();
		$this->main();
	}


	// 商品名入力
	private function inputName()
	{
		echo self::FORMAT["商品入力"];
		$inputData = trim(fgets(STDIN));

		$inputNameValidation = new InputNameValidation($inputData);
		$errorMsg = $inputNameValidation->getErrorMsg();
		if (!empty($errorMsg)) {
			echo $errorMsg;
			return $this->inputName();
		}
		return $inputData;
	}

	// 商品の削除
	private function delete()
	{
		$file = new File;
		$data = $file->readFile();
		
		$countData = count($data);
		if ($countData === 1) {
			echo self::FORMAT["商品不明"];
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
		$this->main();
	}

	// 商品IDの入力
	private function inputId($data)
	{
		echo self::FORMAT["商品削除"];
		$id = trim(fgets(STDIN));

		foreach ($data as $key => $line) {
			if ($key > 0) {
				$idList[] = $line[0];
			}
		}

		$inputMatchValidation = new InputMatchValidation($id, $idList, self::FORMAT["ID不明"]);
		$errorMsg = $inputMatchValidation->getErrorMsg();

		if (!empty($errorMsg)) {
			echo $errorMsg;
			return $this->inputId($data);
		}
		return $id;
	}

	// CSV出力処理
	private function csv()
	{
		$file = new File;
		$file->newCsv();

		echo self::FORMAT["CSV出力"];

		$this->main();
	}

	// CSV読込処理
	private function importCsv()
	{
		$file = new File;
		$file->import();

		$this->main();
	}

	// プログラムの終了
	private function quit()
	{
		$isConfirm = $this->confirm();
		if (!$isConfirm) return $this->main();

		exit(self::FORMAT["操作終了"]);
	}
}


$item = new Item;
$item->main();