PHP = php
HOST = 127.0.0.1
PORT = 8000
DOCROOT = public

default: help

dev:
	@echo "PHP開発サーバーを起動します..."
	@echo "ドキュメントルート: $(DOCROOT)"
	@echo "アクセスURL: http://$(HOST):$(PORT)"
	@echo "サーバーを停止するには Ctrl+C を押してください。"
	$(PHP) -S $(HOST):$(PORT) -t $(DOCROOT)

help:
	@echo ""
	@echo "利用可能なコマンド:"
	@echo "  make dev    - PHP開発サーバーを起動します (http://$(HOST):$(PORT))"
	@echo "  make help   - このヘルプメッセージを表示します"
	@echo ""

.PHONY: default dev help
