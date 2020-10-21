# 商品登録プログラム

## 概要

オブジェクト型指向学習のため、PHPでフルスクラッチ開発した商品登録プログラムです。  
コマンドライン上で操作するもので、CSVのデータ入出力や未入力時等のバリデーション機能を備えています。

## 機能一覧
1. 商品一覧表示
2. 商品登録
3. 商品削除
4. CSVファイル出力
5. CSVファイル読込
6. プログラム終了

## 1.商品一覧表示
【表示項目】
- ID
- 商品名
- JANコード

## 2.商品登録
【入力項目】
- ID（自動）
- 商品名（手動）
- JANコード生成（自動） 
<p>※JANコード生成ルールは、9桁のランダムな数字 + ID３桁</p>
<p>ex) id = 1 のアイテムなら 9桁のランダムな数字 + 001</p>

## 3.商品削除
【入力項目】
- ID

## 4.CSVファイル出力
【出力項目】
- ID
- 商品名
- JANコード
<p>現在登録されている商品一覧をCSVで出力する</p>
<p>パス：：　./csv/item_list_{現在時刻YmdHis}.csv</p>

## 5.CSVファイル読込
- import/というディレクトリを作成し、その中にimport用のcsvファイルを要設置
- もしcsvが１つ以上あったら、importディレクトリ内のファイルをすべて読み込みcsvの内容を上書き登録
 
## 動作方法
 
product_master.php ファイルを実行します。
 
## その他
 
- 初回起動時は必要なディレクトリとデータ保存用に空のcsvが自動生成されます。
- 初めて「6.CSV読込」を選択する際は、必要なディレクトリが自動生成されます。