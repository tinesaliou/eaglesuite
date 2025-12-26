@echo off
REM === Configuration de Java ===
set "JAVA_HOME=C:\Program Files\Java\jdk1.8.0_181"
set "PATH=%JAVA_HOME%\bin;%PATH%"

REM === Chemin vers JasperStarter ===
set "JASPERSTARTER_HOME=C:\jasperstarter"

REM === Chemin des polices ===
set "FONTS_PATH=%JASPERSTARTER_HOME%\lib\fonts"

REM === Classpath complet incluant JasperStarter, MySQL et les polices ===
set "CLASSPATH=%JASPERSTARTER_HOME%\lib\jasperstarter.jar;%JASPERSTARTER_HOME%\lib\mysql-connector-j-8.0.33.jar;%FONTS_PATH%\*"

REM === Exécution de JasperStarter ===
java -cp %CLASSPATH% de.cenote.jasperstarter.App %*
