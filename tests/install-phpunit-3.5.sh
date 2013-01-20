#!/bin/sh

# Creating temp dirs
mkdir .compile_PHPUnit_$$
cd .compile_PHPUnit_$$

# Fetching the archives
wget -nv http://pear.phpunit.de/get/PHPUnit-3.5.15.tgz
wget -nv http://pear.phpunit.de/get/DbUnit-1.0.0.tgz
wget -nv http://pear.phpunit.de/get/File_Iterator-1.2.3.tgz
wget -nv http://pear.phpunit.de/get/Text_Template-1.0.0.tgz
wget -nv http://pear.phpunit.de/get/PHP_CodeCoverage-1.0.2.tgz
wget -nv http://pear.phpunit.de/get/PHP_Timer-1.0.0.tgz
wget -nv http://pear.phpunit.de/get/PHPUnit_MockObject-1.0.3.tgz
wget -nv http://pear.phpunit.de/get/PHPUnit_Selenium-1.0.1.tgz
wget -nv http://pear.phpunit.de/get/PHP_TokenStream-1.1.0.tgz
wget -nv http://pear.symfony-project.com/get/YAML-1.0.2.tgz
wget -nv http://download.pear.php.net/package/XML_RPC2-1.1.1.tgz

# Unpacking
tar -xzf PHPUnit-3.5.15.tgz && echo "Unpacked PHPUnit 3.5.15"
tar -xzf DbUnit-1.0.0.tgz && echo "Unpacked DbUnit 1.0.0"
tar -xzf File_Iterator-1.2.3.tgz && echo "Unpacked File_Iterator 1.2.3"
tar -xzf Text_Template-1.0.0.tgz && echo "Unpacked Text_Template 1.0.0"
tar -xzf PHP_CodeCoverage-1.0.2.tgz && echo "Unpacked PHP_CodeCoverage 1.0.2"
tar -xzf PHP_Timer-1.0.0.tgz && echo "Unpacked PHP_Timer 1.0.0"
tar -xzf PHPUnit_MockObject-1.0.3.tgz && echo "Unpacked PHPUnit_MockObject 1.0.3"
tar -xzf PHPUnit_Selenium-1.0.1.tgz && echo "Unpacked PHPUnit_Selenium 1.0.1"
tar -xzf PHP_TokenStream-1.1.0.tgz && echo "Unpacked PHP_TokenStream 1.1.0"
tar -xzf YAML-1.0.2.tgz && echo "Unpacked YAML 1.0.2"
tar -xzf XML_RPC2-1.1.1.tgz && echo "Unpacked XML_RPC2 1.1.1"

# Creating target dir
mkdir build

# Copying everything to target dir
cp -r PHPUnit-3.5.15/PHPUnit build/
cp -r DbUnit-1.0.0/PHPUnit build/
cp -r PHPUnit_MockObject-1.0.3/PHPUnit build/
cp -r PHPUnit_Selenium-1.0.1/PHPUnit build/
cp -r File_Iterator-1.2.3/File build/
cp -r PHP_CodeCoverage-1.0.2/PHP build/
cp -r PHP_Timer-1.0.0/PHP build/
cp -r Text_Template-1.0.0/Text build/
cp -r YAML-1.0.2/lib build/
cp -r XML_RPC2-1.1.1/XML build/
cp -r PHP_TokenStream-1.1.0/PHP build/

# Creating a package
cp -r build ../phpunit-3.5

# Cleaning up
cd ..
rm -rf .compile_PHPUnit_$$

echo
echo "Done. phpunit-3.5 contains PHPUnit 3.5."
