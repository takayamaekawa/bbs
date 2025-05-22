# Makefile

PHP = php
HOST = 127.0.0.1
# HOST = 0.0.0.0 # LAN内アクセス用
PORT = 8000
DOCROOT = public
PHP_INI_DIR = ./conf/*.ini # ./conf/ ディレクトリ内の全.iniファイルを対象

default: help

dev:
	@echo "PHP開発サーバーを起動します..."
	@echo "ドキュメントルート: $(DOCROOT)"
	@echo "追加読み込みini設定: $(PHP_INI_DIR)"
	@echo "アクセスURL: http://$(HOST):$(PORT)"
	@echo "サーバーを停止するには Ctrl+C を押してください。"
	$(PHP) -S $(HOST):$(PORT) -t $(DOCROOT) -c $(PHP_INI_DIR)

help:
	@echo ""
	@echo "利用可能なコマンド:"
	@echo "  make dev    - PHP開発サーバーを起動 (http://$(HOST):$(PORT))"
	@echo "              追加で $(PHP_INI_DIR) を読み込みます。"
	@echo "  make help   - このヘルプメッセージを表示"
	@echo ""

.PHONY: default dev help
