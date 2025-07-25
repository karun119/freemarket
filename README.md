# フリーマケットアプリ

## 環境構築🔗

### Dockerビルド手順

1. `git clone` git@github.com:karun119/freemarket.git
2. `docker-compose up -d --build`

> ※MySQLは、OSによって起動しない場合があります。  
> 必要に応じて、ご自身のPC環境に合わせて `docker-compose.yml` ファイルを編集してください。

---

### Laravel環境構築手順

1. `docker-compose exec php bash`
2. `composer install`
3. `.env.example` から `.env` を作成し、環境変数を変更  
   `cp .env.example .env`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`

---

### メールテスト環境（MailHog）　📧

MailHog を使用することで、開発中のメール送信内容をローカルのWeb画面で確認できます。
（実際の送信はされません）

### メール送信設定手順
1. `.env` に以下を追記してください

      MAIL_MAILER=smtp  
      MAIL_HOST=mailhog  
      MAIL_PORT=1025  
      MAIL_USERNAME=null  
      MAIL_PASSWORD=null  
      MAIL_ENCRYPTION=null  
      MAIL_FROM_ADDRESS=example@example.com  
      MAIL_FROM_NAME="${APP_NAME}"

2. 設定変更後は以下のコマンドでキャッシュをクリアしてください。

   `php artisan config:clear`

---

## 使用技術🔗

- Laravel: 8.83.29 
- PHP: 8.1.33  
- Composer: 2.8.10  
- MySQL: 8.0.26  
- Nginx: 1.21.1  
- [phpMyAdmin（http://localhost:8080）](http://localhost:8080)
- [MailHog　(http://localhost:8025) ](http://localhost:8025) 
- Docker / Docker Compose

---

## ER図🔗
![index]




---

## URL🔗

- [開発環境 : http://localhost/](http://localhost/)
- [phpMyAdmin : http://localhost:8080/](http://localhost:8080/)
