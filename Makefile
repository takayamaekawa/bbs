# Makefile

PHP = php
HOST = 127.0.0.1
# HOST = 0.0.0.0 # LAN内アクセス用
PORT = 8000
DOCROOT = public
# PHP_INI_DIR は ./conf ディレクトリを指定
PHP_INI_DIR = ./conf

default: help

dev:
	@echo "PHP開発サーバーを起動します..."
	@echo "ドキュメントルート: $(DOCROOT)"
	@echo "追加読み込みini設定ディレクトリ: $(PHP_INI_DIR)"
	@echo "アクセスURL: http://$(HOST):$(PORT)"
	@echo "サーバーを停止するには Ctrl+C を押してください。"
	PHP_INI_SCAN_DIR=$(PHP_INI_DIR) $(PHP) -S $(HOST):$(PORT) -t $(DOCROOT)

help:
	@echo ""
	@echo "利用可能なコマンド:"
	@echo "  make dev    - PHP開発サーバーを起動 (http://$(HOST):$(PORT))"
	@echo "              追加で $(PHP_INI_DIR) 内の .ini ファイルを読み込みます。"
	@echo "  make help   - このヘルプメッセージを表示"
	@echo ""

.PHONY: default dev help
