# Unofficial OXID-CE Archive

This repository contains every version of OXID eSales CE. It is intended for developers who need to quickly test module compatibility with different OXID versions.

These installations are meant for module-development ONLY and should not be used in productive environments. If you need the newest version of OXID eSales or modules, please visit: http://www.oxid-esales.com/de/community/oxid-eshop-herunterladen.html or https://github.com/OXIDprojects


It allows for the following workflow:

    git clone https://github.com/gn2netwerk/OXArchive.git;
    git checkout CE-4.7.3;
    *** COPY/CREATE/TEST YOUR MODULE/DO SOME WORK HERE ***
    git checkout CE-4.6.5;
    *** TEST YOUR SAME WORK IN CE-4.6.5 ***

If you want to see a list of available versions, just type:

    git tag;


As a bonus, each release is tagged for easy downloading: https://github.com/gn2netwerk/OXArchive/tags. This is meant as a mirror of: http://wiki.oxidforge.org/Category:Downloads

If you have any suggestions/improvements, feel free to send a pull request.
