## About SEO generator

Simple ChatGPT powered generator for SEO items like titles, descriptions and keywords

## How to set up a project
After cloning or copying run
- ```composer install```
- copy .env.example file and call it .env
- run ```php artisan key:generate```
- add your API key for your ChatGPT account value to .env file OPENAI_API_KEY= (can be found at https://platform.openai.com/account/api-keys)

## How to use
- place a csv file into **sources** folder
- open a terminal 
- run ```php artisan seo:generate your-csv-file-name-without-whitespaces```
- if you use free ChatGPT account - run command with ```--free``` tag
- pay attention to have only *topic* and *overview* columns in your csv file
- the result file can be found inside **results** under the same name

Considering your file is named test.csv and you have free account in ChatGPT the correct command will look:
```php artisan seo:generate test --free```
