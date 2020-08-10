# Devfolio

    Github 연동을 통해 개발자 포트폴리오를 자동으로 생성합니다.📝

### Screenshot

<kbd><img src='./screenshot/screenshot1.PNG'/></kbd>

### Dev-Environment

|         | PHP | MySQL | Laravel |
|---------|-----|-------|---------|
| Version | 7.4 | 8.0   | 7.x     |

### How to run

##### 1. 프로젝트 clone   
```shell script
git clone https://github.com/HyeminNoh/Devfolio.git
cd Devfolio
```

##### 2. env 파일 세팅   
```shell script
copy .env.example .env
php artisan key:generate
```

* DB Connection Setting
    - DB_CONNECTION=mysql  
      DB_HOST=127.0.0.1 (or your host)  
      DB_PORT=3306  (or your port)  
      DB_DATABASE=Your database name  
      DB_USERNAME=Your user name  
      DB_PASSWORD=Your password  
    
* Github Oauth Setting
    1. **Setting > Developer Settings > OAuth Apps** 메뉴로 이동  
    2. New Oauth App 생성  
        - Homepage URL => http://Your URL  
        - callback URL => http://Your URL/social/github 로 지정
    3. `.env`파일에 값 지정
        - `Client ID` => `.env`파일의 `GITHUB_ID`
        - `Client Secret` => `.env`파일의 `GITHUB_SECRET`
        
##### 3. DB Migration
```shell script
php artisan migrate
```

##### 4. 서버 실행
```shell script
php artisan serve
```
