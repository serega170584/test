# Shop group service

## Запуск проекта

1. Собрать образы php

        make build-images
или
        
        make build-images-force

2. Запустить roadrunner:

         make up-rr
или

         make up-rr-xdebug

3. Запустить postgres

          make up-db

4. Запустить тестовыый postgres

          make up-test-db

5. Запустить kafka

          make up-kafka-infra

6. Запустить redis

         make up-redis

7. Запустить php-cli

         make bash 

или

          make bash-xdebug

8. Настроить mapping путей xdebug в phpstorm
9. Произвести генерацию sdk в случае изменения контрактов proto и команд-запросов
10. В случае возникновения дополнительных вопросов обратиться

      make help
или 
посмотреть infra/projects/shop-group/Makefiles





[![coverage report](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/badges/main/coverage.svg)](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/-/commits/main)
[![pipeline status](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/badges/main/pipeline.svg)](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/-/commits/main)
[![Latest Release](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/-/badges/release.svg)](https://gitlab.com/sprinttechnologies/ecom/backend/shop-group-service/-/releases)
