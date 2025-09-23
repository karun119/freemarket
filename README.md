# フリーマケットアプリ

## 環境構築🔗

### Dockerビルド手順

1. `git clone` git@github.com:karun119/freemarket.git
2. `cd freemarket`
3. `docker-compose up -d --build`

> ※MySQLは、OSによって起動しない場合があります。  
> 必要に応じて、ご自身のPC環境に合わせて `docker-compose.yml` ファイルを編集してください。
M1・M2 Macでのエラー対処法⬇️

`docker-compose.yml` の `mysql` サービスに`platform`を追記してください。
```yaml
mysql:
    platform: linux/x86_64   # ← この行を追加
    image: mysql:8.0.26
    environment:
```
---

### Laravel環境構築手順🔗

1. `docker-compose exec php bash`
2. `composer install`
3. `.env.example` から `.env` を作成 
   `cp .env.example .env`
4. `.env`に以下の環境変数を追加してください。
   ```env
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_pass
   
5. `php artisan key:generate`
6. `php artisan migrate`
7. `php artisan db:seed`
8. シンボリックリンク作成
   `php artisan storage:link`
   

---

### メールテスト環境（MailHog）　📧

MailHog を使用することで、開発中のメール送信内容をローカルのWeb画面で確認できます。
（実際の送信はされません）

### メール送信設定手順🔗
1. `.env` に以下を追記してください
      ```env
      MAIL_MAILER=smtp  
      MAIL_HOST=mailhog  
      MAIL_PORT=1025  
      MAIL_USERNAME=null  
      MAIL_PASSWORD=null  
      MAIL_ENCRYPTION=null  
      MAIL_FROM_ADDRESS=test@example.com  
      MAIL_FROM_NAME="${APP_NAME}"

2. 設定変更後は以下のコマンドでキャッシュをクリアしてください。

   `php artisan config:clear`

---

## アプリのユーザー情報🔗

> 本アプリには管理者ユーザーは存在しません。  
> ※ 新規出品やマイページの商品未出品状態の確認にはユーザー3をご利用ください。


- ユーザー1  (出品あり）
  email: taro@example.com  
  password: password1  

- ユーザー2  （出品あり）
  email: hanako@example.com  
  password: password2  

- ユーザー3  （出品なし・新規出品テスト用)
  email: jiro@example.com  
  password: password3  

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


![index](https://github.com/user-attachments/assets/da3be915-4652-4c17-8e87-3ae3d0b67b79)

---

## URL🔗

- [開発環境 : http://localhost/](http://localhost/)
- [phpMyAdmin : http://localhost:8080/](http://localhost:8080/)
