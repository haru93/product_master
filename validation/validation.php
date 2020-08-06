<?php
class BaseValidation
{
	protected $inputData;
	protected $errorMsg;
	
	public function emptyValidate($format)
	{
		if (empty($this->inputData)) {
			$this->errorMsg = $format;
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

// バリデーション
class InputNameValidation extends BaseValidation
{
	public function __construct($inputData)
	{
		$this->inputData = $inputData;
		$this->emptyValidate(Item::FORMAT["値未入力"]);
	}
}


// 入力値が指定されている場合のバリデーション
class InputMatchValidation extends BaseValidation
{
	private $match;
	private $format;

	public function __construct($inputData, $match, $format)
	{
		$this->inputData = $inputData;
		$this->match = $match;
		$this->format = $format;

		$this->emptyValidate(Item::FORMAT["値未入力"]);
		$this->matchValidate();
	}

	private function matchValidate()
	{
		if (!in_array($this->inputData, $this->match, true)) {
			$this->errorMsg = $this->format;
			return;
		}
	}
}


class ImportCsvValidation extends BaseValidation
{
	public function __construct($inputData)
	{
		$this->inputData = $inputData;
		$this->emptyValidate(Item::FORMAT["読込用データ不明"]);
	}
}