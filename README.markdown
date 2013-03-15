# Unofficial OXID-CE Archive

This repository contains every version of OXID eSales CE. It is intended for developers who need to quickly test module compatibility with different OXID versions.

It allows for the following workflow:

    git clone https://github.com/gn2netwerk/OXArchive.git;
    git checkout git checkout CE-4.7.3;
    *** COPY/CREATE/TEST YOUR MODULE/DO SOME WORK HERE ***
    git checkout git checkout CE-4.6.5;
    *** TEST YOUR SAME WORK IN CE-4.6.5 ***

If you want to see a list of available versions, just type:

    git tag;

If you have any suggestions/improvements, feel free to send a pull request.
