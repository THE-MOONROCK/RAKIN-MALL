@echo

docker-compose -f docker-compose-prod.yml build

docker login repo.poscarcloud.com

docker-compose -f docker-compose-prod.yml push
