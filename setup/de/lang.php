<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link http://www.oxid-esales.com
 * @package setup
 * @copyright © OXID eSales AG 2003-2008
 * $Id: lang.php 13844 2008-10-29 11:16:55Z ralf $
 */

$aLang = array(

'charset'                                         => 'iso-8859-1',
'HEADER_META_MAIN_TITLE'                          => "OXID eShop Installationsassistent",
'HEADER_TEXT_SETUP_NOT_RUNS_AUTOMATICLY'          => "Sollte das Setup nicht nach einigen Sekunden automatisch weiterspringen, dann klicken Sie bitte",
'FOOTER_OXID_ESALES'                              => "&copy; OXID eSales AG 2008",

'TAB_1_TITLE'                                     => "Willkommen",
'TAB_2_TITLE'                                     => "Lizenzbedingungen",
'TAB_3_TITLE'                                     => "Datenbank",
'TAB_4_TITLE'                                     => "Verzeichnisse",
'TAB_5_TITLE'                                     => "Lizenz",
'TAB_6_TITLE'                                     => "Fertigstellen",

'TAB_1_DESC'                                      => "Herzlich willkommen<br>zur Installation von OXID eShop",
'TAB_2_DESC'                                      => "Best&auml;tigen Sie die Lizenzbedingungen",
'TAB_3_DESC'                                      => "Verbindung testen,<br>Tabellen anlegen",
'TAB_4_DESC'                                      => "Einrichten Ihres Shops,<br>Schreiben der Konfigurationsdatei",
'TAB_5_DESC'                                      => "Lizenzschlüssel eintragen",
'TAB_6_DESC'                                      => "Installation erfolgreich",

'HERE'                                            => "hier",

'ERROR_NOT_AVAILABLE'                             => "FEHLER: %s nicht vorhanden!",
'ERROR_CHMOD'                                     => "FEHLER: Kann %s nicht auf chmod(0755) setzen!",
'ERROR_NOT_WRITABLE'                              => "FEHLER: %s nicht beschreibbar!",
'ERROR_DB_CONNECT'                                => "FEHLER: Keine Datenbank Verbindung möglich!",
'ERROR_OPENING_SQL_FILE'                          => "FEHLER: Kann SQL Datei %s nicht öffnen!",
'ERROR_FILL_ALL_FIELDS'                           => "FEHLER: Bitte alle notwendigen Felder ausf&uuml;llen!",
'ERROR_COULD_NOT_CONNECT_TO_DB'                   => "FEHLER: Keine Datenbank Verbindung möglich!",
'ERROR_COULD_NOT_CREATE_DB'                       => "FEHLER: Datenbank %s nicht vorhanden und kann auch nicht erstellt werden!",
'ERROR_DB_ALREADY_EXISTS'                         => "FEHLER: Es scheint als ob in der Datenbank %s bereits eine OXID Datenbank vorhanden ist. Bitte löschen Sie diese!",
'ERROR_BAD_SQL'                                   => "FEHLER: (Tabellen)Probleme mit folgenden SQL Befehlen: ",
'ERROR_BAD_DEMODATA'                              => "FEHLER: (Demodaten)Probleme mit folgenden SQL Befehlen: ",
'ERROR_CONFIG_FILE_IS_NOT_WRITABLE'               => "FEHLER: %s/config.inc.php"." nicht beschreibbar!",
'ERROR_BAD_SERIAL_NUMBER'                         => "FEHLER: Falsche Serienummer!",
'ERROR_COULD_NOT_OPEN_CONFIG_FILE'                => "Konnte config.inc.php nicht &ouml;ffnen. Bitte in unserer FAQ oder im Forum nachlesen oder den OXID Support kontaktieren.",

'STEP_1_TITLE'                                    => "Willkommen",
'STEP_1_DESC'                                     => "Willkommen beim Installationsassistenten für den OXID eShop",
'STEP_1_TEXT'                                     => "<p>Um eine erfolgreiche und einfache Installation zu gewährleisten, nehmen Sie sich bitte die Zeit, die folgenden Punkte aufmerksam zu lesen und Schritt für Schritt auszuführen.</p> <p>Viel Erfolg mit Ihrem OXID eShop w&uuml;nscht Ihnen</p>",
'STEP_1_ADDRESS'                                  => "OXID eSales AG<br>
                                                      Bertoldstr. 48<br>
                                                      79098 Freiburg<br>
                                                      Deutschland<br>",
'BUTTON_BEGIN_INSTALL'                            => "Shopinstallation beginnen",

'STEP_2_TITLE'                                    => "Lizenzbedingungen",
'BUTTON_RADIO_LICENCE_ACCEPT'                     => "Ich akzeptiere die Lizenzbestimmungen.",
'BUTTON_RADIO_LICENCE_NOT_ACCEPT'                 => "Ich akzeptiere die Lizenzbestimmungen nicht.",
'BUTTON_LICENCE'                                  => "Lizenzbedingungen annehmen",

'STEP_3_TITLE'                                    => "Datenbank",
'STEP_3_DESC'                                     => "Nun wird die Datenbank erstellt und mit den notwendigen Tabellen bef&uuml;llt. Dazu ben&ouml;tigen wir einige Angaben von Ihnen:",
'STEP_3_DB_HOSTNAME'                              => "Datenbank Hostname oder IP Adresse",
'STEP_3_DB_USER_NAME'                             => "Datenbank Benutzername",
'STEP_3_DB_PASSWORD'                              => "Datenbank Passwort",
'STEP_3_DB_DATABSE_NAME'                          => "Datenbank Name",
'STEP_3_DB_DEMODATA'                              => "Demodaten",
'STEP_3_CREATE_DB_WHEN_NO_DB_FOUND'               => "Falls die Datenbank nicht vorhanden ist, wird versucht diese anzulegen",
'BUTTON_RADIO_INSTALL_DB_DEMO'                    => "Demodaten installieren",
'BUTTON_RADIO_NOT_INSTALL_DB_DEMO'                => "Demodaten <strong>nicht</strong> installieren",
'BUTTON_DB_INSTALL'                               => "Datenbank jetzt erstellen",

'STEP_3_1_TITLE'                                  => "Datenbank - in Arbeit...",
'STEP_3_1_DB_CONNECT_IS_OK'                       => "Datenbank Verbindung erfolgreich geprüft...",
'STEP_3_1_DB_CREATE_IS_OK'                        => "Datenbank %s erfolgreich erstellt...",
'STEP_3_1_CREATING_TABLES'                        => "Erstelle Tabellen, kopiere Daten...",

'STEP_3_2_TITLE'                                  => "Datenbank - Tabellen erstellen...",
'STEP_3_2_CONTINUE_INSTALL_OVER_EXISTING_DB'      => "Falls Sie dennoch installieren wollen und die alten Daten überschreiben, klicken Sie",
'STEP_3_2_CREATING_DATA'                          => "Datenbank erfolgreich erstellt!<br>Bitte warten...",

'STEP_4_TITLE'                                    => "Einrichten des OXID eShops",
'STEP_4_DESC'                                     => "Bitte geben Sie hier die für den Betrieb notwendigen Daten ein:",
'STEP_4_SHOP_URL'                                 => "Shop URL",
'STEP_4_SHOP_DIR'                                 => "Verzeichnis auf dem Server zum Shop",
'STEP_4_SHOP_TMP_DIR'                             => "Verzeichnis auf dem Server zum TMP Verzeichnis",
'STEP_4_DELETE_SETUP_DIR'                         => "Den Setup Ordner automatisch entfernen",

'STEP_4_1_TITLE'                                  => "Verzeichnisse - in Arbeit...",
'STEP_4_1_DATA_WAS_WRITTEN'                       => "Kontrolle und Schreiben der Dateien erfolgreich!<br>Bitte warten...",
'BUTTON_WRITE_DATA'                               => "Daten jetzt speichern",

'STEP_5_TITLE'                                    => "OXID eShop Lizenz",
'STEP_5_DESC'                                     => "Bitte geben Sie nun Ihren OXID eShop Lizenzschlüssel ein:",
'STEP_5_LICENCE_KEY'                              => "Lizenzschl&uuml;ssel",
'STEP_5_LICENCE_DESC'                             => "Der mit der Demo Version ausgelieferte Lizenzschl&uuml;ssel (oben bereits ausgefüllt) ist 30 Tage gültig .<br>
                                                      Nach Ablauf der 30 Tage können alle Ihre Änderungen nach Eingabe eines gültigen Lizenzschl&uuml;ssels weiterhin benutzt werden.",
'BUTTON_WRITE_LICENCE'                            => "Lizenzschl&uuml;ssel speichern",

'STEP_5_1_TITLE'                                  => "Lizenzschl&uuml;ssel - in Arbeit...",
'STEP_5_1_SERIAL_ADDED'                           => "Lizenzschl&uuml;ssel erfolgreich gespeichert!<br>Bitte warten...",

'STEP_6_TITLE'                                    => "OXID eShop Einrichtung erfolgreich",
'STEP_6_DESC'                                     => "Die Einrichtung Ihres OXID eShops wurde erfolgreich abgeschlossen.",
'STEP_6_LINK_TO_SHOP'                             => "Hier geht es zu Ihrem Shop",
'STEP_6_LINK_TO_SHOP_ADMIN_AREA'                  => "Zugang zu Ihrer Shop Administration",
'STEP_6_TO_SHOP'                                  => "Zum Shop",
'STEP_6_TO_SHOP_ADMIN'                            => "Zur Shop Administration",

'ATTENTION'                                       => "Bitte beachten Sie",
'SETUP_DIR_DELETE_NOTICE'                         => "WICHTIG: Bitte l&ouml;schen Sie Ihr Setup Verzeichnis falls dieses nicht bereits automatisch entfernt wurde!",

'SELECT_SETUP_LANG'                               => "Sprache für die Installation",
'SELECT_COUNTRY_LANG'                             => "Ihr Standort",
'SELECT_SETUP_LANG_SUBMIT'                        => "Ausw&auml;hlen",
'USE_DYNAMIC_PAGES'                               => "Um Ihren Geschäftserfolg zu vergr&ouml;&szlig;ern, laden Sie weitere Informationen vom OXID Server nach. <br>Mehr Informationen in unserern ",
'PRIVACY_POLICY'                                  => "Datenschutzerl&auml;uterungen",

'LOAD_DYN_CONTENT_NOTICE'                         => "<p>Falls die Option &quot;Weitere Informationen&quot; nachladen eingeschaltet ist, sehen Sie ein zus&auml;tzliches Men&uuml; im Admin Bereich Ihres OXID eShops.</p><p>Mittels dieses Men&uuml;s erhalten Sie weitere Informationen &uuml;ber E-Commerce Services wie z.B. Google Produktsuche oder econda.</p> <p>Sie k&ouml;nnen diese Einstellung im Admin Bereich jederzeit wieder &auml;ndern.</p>",

);

?>
