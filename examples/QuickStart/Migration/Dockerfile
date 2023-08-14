FROM flyway/flyway:latest

ENV MYSQL_DRIVER_VERSION 8.1.0

RUN curl -o drivers/mysql-connector-java-${MYSQL_DRIVER_VERSION}.jar \
    https://repo1.maven.org/maven2/mysql/mysql-connector-java/${MYSQL_DRIVER_VERSION}/mysql-connector-java-${MYSQL_DRIVER_VERSION}.jar