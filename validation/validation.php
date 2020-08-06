<?php
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
			$this->errorMsg = Item::FORMAT["値未入力"];
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
			$this->errorMsg = Item::FORMAT["値未入力"];
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
			$this->errorMsg = Item::FORMAT["読込用データ不明"];
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