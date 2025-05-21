<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
  ->in(__DIR__)
  ->exclude('vendor')
  ->notPath('bootstrap/cache')
  ->notPath('storage')
;

return (new Config())
  ->setRules([
    /* '@PSR12' => true, // PSR-12 標準に準拠 */

    // インデント関連のルール (editorconfig と合わせてスペース2つ)
    'indentation_type' => true, // スペースインデントを使用
    'array_indentation' => true, // 配列のインデントを修正
    'method_chaining_indentation' => true, // メソッドチェーンのインデントを修正
    'statement_indentation' => true, // 通常のステートメントのインデントを修正
    'no_whitespace_in_blank_line' => true, // 空行に空白なし
    'no_trailing_whitespace' => true, // 行末の空白を削除

    // その他のルール (エラーを修正済み)
    'full_opening_tag' => false, // 短縮形ではなくフルオープンタグを使用
    'concat_space' => ['spacing' => 'one'], // 文字列連結演算子(`.`)の前後にスペースを1つ
    'array_syntax' => ['syntax' => 'short'], // 短い配列構文 `[]` を使用
    'ordered_imports' => ['sort_algorithm' => 'alpha'], // use ステートメントをアルファベット順にソート
    'no_unused_imports' => true, // 未使用の use ステートメントを削除
    'binary_operator_spaces' => [ // 二項演算子の前後にスペースを追加
      'default' => 'single_space',
      'operators' => ['=>' => null], // => 演算子は例外（調整可）
    ],
    'blank_line_after_namespace' => true, // namespace の後に空行
    'blank_line_after_opening_tag' => true, // 開始タグの後に空行
    'braces' => [ // 波括弧のスタイル
      'allow_single_line_closure' => true,
      // ここを修正: 関数の波括弧を同じ行に記述
      'position_after_functions_and_oop_constructs' => 'same', // ★ここを 'same' に変更
      'position_after_control_structures' => 'same',
      'position_after_anonymous_constructs' => 'same',
    ],
    'cast_spaces' => ['space' => 'none'], // キャストのスペース
    'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']], // クラス属性間の空行
    'compact_nullable_typehint' => true, // null許容型ヒントのコンパクト化
    'declare_equal_normalize' => ['space' => 'none'],
    'elseif' => true, // else if を elseif に
    'encoding' => true, // ファイルエンコーディング
    'full_opening_tag' => true, // フルオープンタグ
    'function_declaration' => ['closure_function_spacing' => 'one'], // 関数宣言のスペース
    'linebreak_after_opening_tag' => true, // PHP開始タグの後に改行
    'lowercase_cast' => true, // キャストを小文字に
    'constant_case' => ['case' => 'lower'], // 定数を小文字に
    'lowercase_keywords' => true, // キーワードを小文字に
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'], // メソッド引数のスペース
    'no_leading_namespace_whitespace' => true, // 名前空間の先頭の空白なし
    'no_blank_lines_after_class_opening' => true, // クラス開始後の空白行なし
    'no_extra_blank_lines' => ['tokens' => ['extra']], // 余分な空行なし
    'no_leading_import_slash' => true, // 先頭の import スラッシュなし
    'normalize_index_brace' => true, // 配列インデックスのブレースを正規化
    'object_operator_without_whitespace' => true, // オブジェクト演算子の空白なし
    'single_quote' => true, // シングルクォートを使用
    'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],
    'trailing_comma_in_multiline' => ['elements' => ['arrays']], // 多行の末尾カンマ
    'trim_array_spaces' => true, // 配列のスペースをトリム
    'unary_operator_spaces' => true, // 単項演算子のスペース
    'whitespace_after_comma_in_array' => true, // 配列のカンマ後の空白
  ])
  ->setLineEnding("\n") // 改行コードをLFに統一 (editorconfigと合わせる)
  ->setIndent('  ') // インデントをスペース2つに設定 (editorconfigと合わせる)
  ->setUsingCache(true)
  ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache') // キャッシュファイルの場所
  ->setFinder($finder)
;
