Bolido
======

[![Build Status](https://secure.travis-ci.org/mpratt/Bolido.png)](http://travis-ci.org/mpratt/Bolido)

Bolido es un pseudo-framework. Su objetivo principal es crear un entorno modular y sencillo, que facilite el
desarrollo de aplicaciones web con PHP.

Actualmente Bolido esta enfocado para trabajar con Apache y MySQL/PostgreSQL, pero la idea es eventualmente soportar
otros servidores y bases de datos.

Bolido usa injeccion de dependencias, eso quiere decir que la mayoría de los componentes pueden ser usados independientemente
con muy poco esfuerzo. Adicionalmente, usa un patron de diseño MVC y pretende seguir las reglas del desarrollo tipo SOLID.

Aunque he estado usando este framework en varios proyectos (sobretodo personales), probando asi su estabilidad,
hay que tener en cuenta que aún esta en un proceso experimental y aún le falta madurar un poco más, antes de ser
usado en aplicaciones complejas.

Por ahora no tengo mucha documentación/tutoriales sobre esta aplicación, sinembargo todos los archivos estan propiamente
comentados con Docblocks. Los tests cubren el 100% del Framework. Para guiarse, la aplicación tiene un modulo llamado 'main'
que puede ser visto como una guia para crear más modulos. Es importante no borrar/modificar ese modulo, pues tiene un par de funciones
utiles para todo el framework.

Requerimientos
==============

 - PHP >= 5.4
 - Una base de datos MySQL/PostgreSQL
 - Apache (con mod rewrite activado)

Instalacion
===========

Se puede usar composer para instalarlo

    curl -s https://getcomposer.org/installer | php
    php composer.phar create-project mpratt/bolido

Otra manera de instalacion es clonar el repositorio:

    git clone git://github.com/mpratt/Bolido.git

Debes modificar el archivo Config-sample.php con la información correspondiente a tu entorno y luego
renombrarlo a Config.php. Finalmente tambien debes renombrar el archivo htaccess-sample a .htaccess.

Si todo esta bien, al ir al inicio de tu dirección web podras ver un aviso del Framework junto con algunas
pruebas de entorno.

Licencia
========

Esta aplicación esta protegida bajo una licencia MIT.
Para mayor información ver el archivo LICENSE.

Autor
=====

Michael Pratt

[Página Personal](http://www.michael-pratt.com)
