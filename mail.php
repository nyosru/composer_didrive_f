<?php

namespace Nyos\mod;

use f as f;
use Sendpulse as Sendpulse;

//if (!defined('IN_NYOS_PROJECT'))
//    die('Сработала защита <b>class MySQL</b> от злостных розовых хакеров.
//	<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут... ..');

define("class_mail2", true);

class mailpost {

    public static $body = '';
    public static $msg, $subject, $extra_headers;
    // массив для новых файлов 
    // $new_file[] = array( 'name' => '1111.111','contant' => 'abc' ); 
    public static $new_file = array();
    public static $addresses, $reply_to, $from;
    public static $template;
    // ключи для отправки по сендпульс АПИ смтп
    public static $sendpulse_id = null;
    public static $sendpulse_key = null;
    public static $ns = array();
    var $tpl_msg = array();

    function clear() {
        self::$body = self::$msg = self::$subject = self::$extra_headers = self::$addresses = self::$reply_to = self::$from = self::$template = null;

        // ключи для отправки по сендпульс АПИ смтп
        self::$sendpulse_id = null;
        self::$sendpulse_key = null;
        self::$ns = array();
        self::$tpl_msg = array();
    }

    /**
     * добавление файла
     * @param type $content
     */
    public static function addNewFile($name, $content) {

        self::$new_file[] = array('name' => $name, 'content' => $content);
    }

    public static function mailpost() {
        self::reset();
        self::$reply_to = self::$from = '';
    }

    public static function ns_new($from, $to) {
        self::$ns['from'] = $from;
        self::$ns['to'] = $to;
    }

    public static function ns_send($tema, $tpl_telo, $from_domain = null) {

        //global $status;
        $status = '';
        //echo '<pre>'.htmlspecialchars($ClassTemplate->tpl_files[$tpl_telo]).'</pre>';
        //echo strlen(self::$msg);
        //self::$msg = str_replace ("'", "\'", self::$msg);
        //self::$msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", self::$msg);
        //self::$subject = (($tema != '') ? $tema : 'No Subject');
        // echo '<hr>'.__LINE__.'<hr>'.$tema;
        // f\pa($tema);

        self::$subject = (($tema != '') ? '=?cp1251?B?' .
                //self::$subject = (($tema != '') ? '=?utf-8?B?' . 
                //base64_encode(convert_cyr_string $tema, «w»,«k»)
                base64_encode(iconv('utf-8', 'windows-1251', $tema))
                // base64_encode(iconv('utf-8', 'koi8-r', $tema)) 
                // $tema
                . '?=' : 'No Subject');

        self::$extra_headers = ''
                //. 'MIME-Version: 1.0' . PHP_EOL
                . 'From: ' . self::$ns['from'] . "\n"
                . 'Replay-To: ' . self::$ns['from'] . "\n"
                . 'Return-Path: ' . self::$ns['from'] . "\n"
                . 'Message-ID: <' . md5(uniqid($_SERVER['REQUEST_TIME'])) . '@' . ( $from_domain !== null ? $from_domain : $_SERVER['HTTP_HOST'] ) . '>' . "\n"
                . 'Date: ' . date('r', $_SERVER['REQUEST_TIME']) . "\n"
                . 'X-Priority: 3' . "\n"
                . 'X-MSMail-Priority: Normal' . "\n"
                . 'X-Mailer: NYOSmail' . "\n"
                . 'X-MimeOLE: Produced by NYOS' . "\n"
        ;



        $cc = ( isset(self::$addresses['cc']) && sizeof(self::$addresses['cc']) > 0 ) ? implode(', ', self::$addresses['cc']) : '';

        if ($cc != '')
            self::$extra_headers .= 'Cc: ' . $cc . '\n';

        $bcc = ( isset(self::$addresses['bcc']) && sizeof(self::$addresses['bcc']) > 0 ) ? implode(', ', self::$addresses['bcc']) : '';

        if ($bcc != '')
            self::$extra_headers .= 'Bcc: ' . $bcc . '\n';




        // если есть вложения
        if (sizeof(self::$new_file) > 0) {

            $status .= '<Br/><Br/>Найдены файлы, начинаем отправлять с вложениями<Br/><Br/>';
            //echo __FILE__.' #'.__LINE__;
//Разделитель
            //$boundary = "---";
            $boundary = rand(100, 999);

            self::$extra_headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . PHP_EOL;

            //$emessage .= "Content-Transfer-Encoding: 7bit\n\n";
//
//            self::$msg = '--' . $boundary . PHP_EOL;
//            /* Присоединяем текстовое сообщение */
//            self::$msg .= 'Content-type: text/html; charset=\'utf-8\'' . PHP_EOL
//                    . 'Content-Transfer-Encoding: quoted-printablen' . PHP_EOL
//                    //. 'Content-Transfer-Encoding: 7bit' . PHP_EOL
//                    // . 'Content-Disposition: attachment; filename==?utf-8?B?' . base64_encode($filename) . '?=' . PHP_EOL . PHP_EOL
//                    . $tpl_telo . PHP_EOL // . PHP_EOL
//            ;
//
//            self::$msg .= '--' . $boundary . PHP_EOL;

            if (1 == 2) {
                self::$msg .= '--' . $boundary . PHP_EOL;

                if (sizeof(self::$new_file) > 0) {

                    foreach (self::$new_file as $k => $v) {

                        //self::$msg .= '--'.$boundary.PHP_EOL;
//                    $file = fopen($filename, "r"); //Открываем файл
//                    $text = fread($file, filesize($filename)); //Считываем весь файл
//                    fclose($file); //Закрываем файл

                        /* Добавляем тип содержимого, кодируем текст файла и добавляем в тело письма */
                        self::$msg .= 'Content-Type: application/octet-stream; name==?utf-8?B?' . base64_encode($v['name']) . '?=' . PHP_EOL
                                . 'Content-Transfer-Encoding: base64' . PHP_EOL
                                . 'Content-Disposition: attachment; filename==?UTF-8?B?' . base64_encode($v['name']) . '?=' . PHP_EOL // . PHP_EOL
                                . chunk_split(base64_encode($v['content'])) . PHP_EOL;

                        self::$msg .= '--' . $boundary . PHP_EOL;
                    }
                }
            }

//            $body .= "--$boundary\n";
//            $file = fopen($filename, "r"); //Открываем файл
//            $text = fread($file, filesize($filename)); //Считываем весь файл
//            fclose($file); //Закрываем файл
//            /* Добавляем тип содержимого, кодируем текст файла и добавляем в тело письма */
//            $body .= "Content-Type: application/octet-stream; name==?utf-8?B?" . base64_encode($filename) . "?=\n";
//            $body .= "Content-Transfer-Encoding: base64\n";
//            $body .= "Content-Disposition: attachment; filename==?utf-8?B?" . base64_encode($filename) . "?=\n\n";
//            $body .= chunk_split(base64_encode($text)) . "\n";
//            $body .= "--" . $boundary . "--\n";
            // self::$msg = $tpl_telo;
            // $filename = "form.txt"; //Имя файла для прикрепления
            // $to = "abc@mail.ru"; //Кому
            // $from = "def@gmail.com"; //От кого
            // $subject = "Test"; //Тема
            // $message = "Текстовое сообщение"; //Текст письма
            //$boundary = "---"; //Разделитель


            /* Заголовки */
//            self::$extra_headers = 'From: '.$from . PHP_EOL // ."\n"
//                .'Reply-To: '. $from . PHP_EOL // ."\n"
            //;
            //self::$extra_headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"'.PHP_EOL.PHP_EOL;

            self::$msg = '--' . $boundary . PHP_EOL
                    /* Присоединяем текстовое сообщение */
                    //. 'Content-type: text/html; charset=\'utf-8\''.PHP_EOL
                    //. 'Content-type: text/html; charset=\'utf-8\''.PHP_EOL
                    . 'Content-type: text/html' . PHP_EOL
                    . 'Content-Transfer-Encoding: quoted-printable' . PHP_EOL . PHP_EOL
                    // . "Content-Disposition: attachment; filename==?utf-8?B?" . base64_encode($filename) . "?=\n\n"
                    //. $message . "\n";
                    . $tpl_telo . PHP_EOL . PHP_EOL;

            if (sizeof(self::$new_file) > 0) {

                foreach (self::$new_file as $k => $v) {

                    self::$msg .= '--' . $boundary . PHP_EOL;

                    /*
                      $file = fopen($filename, "r"); //Открываем файл
                      $text = fread($file, filesize($filename)); //Считываем весь файл
                      fclose($file); //Закрываем файл
                     */

                    /* Добавляем тип содержимого, кодируем текст файла и добавляем в тело письма */
                    self::$msg .= 'Content-Type: application/octet-stream; name==?utf-8?B?' . base64_encode($v['name']) . '?=' . PHP_EOL
                            . 'Content-Transfer-Encoding: base64' . PHP_EOL
                            . 'Content-Disposition: attachment; filename==?utf-8?B?' . base64_encode($v['name']) . '?=' . PHP_EOL . PHP_EOL
                            . chunk_split(base64_encode($v['content'])) . PHP_EOL;
                }
            }

            // self::$msg .= "--" . $boundary . "--\n";
            // mail($to, $subject, self::$msg, $headers); //Отправляем письмо
            // return true;
        }
// если без вложений
        else {
            self::$extra_headers .= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
            self::$msg = $tpl_telo;
        }

        $result = @mail(
                        self::$ns['to'] . (( $cc != '' ) ? $cc : '' ), self::$subject, self::$msg, self::$extra_headers, '-fno-reply@' . ( $from_domain !== null ? $from_domain : $_SERVER['HTTP_HOST'] ));

        if (!$result) {

            $result = @mail(
                            $to, self::$subject, self::$msg, self::$extra_headers, '-fno-reply@' . ( $from_domain !== null ? $from_domain : $_SERVER['HTTP_HOST'] )
            );

            if (!$result) {
                $status .= '[' . __LINE__ . '] email не отправлен даже со 2-ой попытки.<br>';
                return false;
            } else {
                return TRUE;
            }
        } else {

            $status .= '[' . __LINE__ . '] email удачно отправлен с 1-го раза.<br>';
            return true;
        }

        return true;
    }

    public static function ns_send_plain($tema, $tpl_telo) {
        global $status, $my;

        self::$msg = $tpl_telo;

        self::$msg = str_replace("'", "\'", self::$msg);
        self::$msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", self::$msg);
        self::$subject = (($tema != '') ? $tema : 'No Subject');

        self::$extra_headers = "MIME-Version: 1.0\r\n";
        self::$extra_headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        self::$extra_headers .= "From: " . $this->ns['from'] . "\r\n";

        $result = mail(self::$ns['to'], self::$subject, self::$msg, self::$extra_headers);

        if (!$result && $empty_to_header) {
            $result = mail($to, self::$subject, self::$msg, self::$extra_headers);

            if (!$result) {
                $status .= '[' . __LINE__ . '] email не отправлен даже со 2-ой попытки.<br>';
                return false;
            } else {
                return TRUE;
            }
        } else {
            $status .= '[' . __LINE__ . '] email удачно отправлен с 1-го раза.<br>';
            return true;
        }

        return true;
    }

    public static function reset() {
        self::$addresses = array();
        self::$vars = self::$msg = self::$extra_headers = '';
    }

    public static function email_address($address) {
        self::$addresses['to'] = trim($address);
    }

    public static function cc($address) {
        self::$addresses['cc'][] = trim($address);
    }

    public static function bcc($address) {
        self::$addresses['bcc'][] = trim($address);
    }

    public static function replyto($address) {
        self::$reply_to = trim($address);
    }

    public static function from($address) {
        self::$from = trim($address);
    }

    // set up subject for mail
    public static function set_subject($subject = '') {
        self::$subject = trim(preg_replace('#[\r]+#s', '', $subject));
    }

    // set up extra mail headers
    public static function extra_headers($headers) {
        self::$extra_headers .= trim($headers) . "\r\n";
    }

    public static function use_template($template_file) {
        self::$template = $template_file;
        self::$body = 'template';
    }

    public static function use_template_v($var) {
        self::$template_v = $var;
        self::$body = 'var';
    }

    // Send the mail out to the recipients set previously in var self::$address
    public static function send() {
        global $ctpl, $status, $my;
        $status .= '<table><tr><td width="15" bgcolor="#2B75D0" align="center" nowrap style="direction: ltr; writing-mode:  tb-rl;"><strong><a href="' . __FILE__ . '" title="' . __FILE__ . ' [' . __LINE__ . ']">emailer.send</a></strong></td><td bgcolor="#D5E2F7">';

        if (self::$body == 'template')
            self::$msg = $ctpl->tpl_files[self::$template];

        if (self::$body == 'var')
            self::$msg = self::$template_v;

        $status .= '|<a href="' . __FILE__ . '" title="' . __FILE__ . ' [' . __LINE__ . ']"> class emailer </a>| в основе сообщения шаблон <strong>' . self::$template . '</strong><br>';

        // Escape all quotes, else the eval will fail.
        self::$msg = str_replace("'", "\'", self::$msg);
        self::$msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", self::$msg);

        // We now try and pull a subject from the email body ... if it exists,
        // do this here because the subject may contain a variable
        $drop_header = '';
        $match = array();
        if (preg_match('#^(Subject:(.*?))$#m', self::$msg, $match)) {
            self::$subject = (trim($match[2]) != '') ? trim($match[2]) : ((self::$subject != '') ? self::$subject : 'No Subject');
            $drop_header .= '[\r\n]*?' . phpbb_preg_quote($match[1], '#');
        } else {
            self::$subject = ((self::$subject != '') ? self::$subject : 'No Subject');
        }
        $status .= '| class emailer [' . __LINE__ . ']| тема - <strong><u>' . self::$subject . '</u></strong><br>';

        if ($drop_header != '') {
            self::$msg = trim(preg_replace('#' . $drop_header . '#s', '', self::$msg));
        }

        $to = self::$addresses['to'];
        $cc = (count(self::$addresses['cc'])) ? implode(', ', self::$addresses['cc']) : '';
        $bcc = (count(self::$addresses['bcc'])) ? implode(', ', self::$addresses['bcc']) : '';

        // Build header
        self::$extra_headers = ((self::$reply_to != '') ? "Reply-to: self::$reply_to\n" : '')
                .
                ((self::$from != '') ? "From: self::$from\n" : "From: " . $my["mail_support"] . "\n")
                . "Return-Path: " . $my["mail_support"] . "\nMessage-ID: <" . md5(uniqid(time())) . "@" . $board_config['server_name'] . ">\nMIME-Version: 1.0\nContent-type: text/plain; charset=utf-8\nContent-transfer-encoding: 8bit\nDate: " . date('r', time()) . "\nX-Priority: 3\nX-MSMail-Priority: Normal\nX-Mailer: system NYOS\nX-MimeOLE: Produced by system NYOS\n" . self::$extra_headers . (($cc != '') ? "Cc: $cc\n" : '') . (($bcc != '') ? "Bcc: $bcc\n" : '');

        $empty_to_header = ($to == '') ? TRUE : FALSE;
        $to = ($to == '') ? (($board_config['sendmail_fix']) ? ' ' : 'Undisclosed-recipients:;') : $to;
        $result = @mail($to, self::$subject, preg_replace("#(?<!\r)\n#s", "\n", self::$msg), self::$extra_headers);

        if (!$result && $empty_to_header) {
            $to = ' ';
            $result = @mail($to, self::$subject, preg_replace("#(?<!\r)\n#s", "\n", self::$msg), self::$extra_headers);

            if (!$result) {
                $status .= '[' . __LINE__ . '] email не отправлен даже со 2-ой попытки.<br>';
            }
        } else {
            $status .= '[' . __LINE__ . '] email удачно отправлен с 1-го раза.<br>';
        }

        $status .= '[' . __LINE__ . '] END return TRUE<br>';
        $status .= '</td></tr></table> ';
        return true;
    }

    /**
     * функция отправки 1812
     * @param type $from
     * @param type $to
     * @param type $subj
     * @param type $text
     * @param type $filename
     * ссылка на файл на сервере или массив ( 1 => ф, 2 => ф )
     * @return type
     */
    public static function sendHtmlMail($from, $to, $subj, $text, $filename = null) {

        $un = strtoupper(uniqid(time()));
        $head = "From: $from\n";
        // $head .= "To: $to\n";
        $head .= "Subject: $subj\n";
        $head .= "X-Mailer: Nyos Tool\n";
        $head .= "Reply-To: $from\n";
        $head .= "Mime-Version: 1.0\n";
        $head .= "Content-Type:multipart/mixed;";
        $head .= "boundary=\"----------" . $un . "\"\n\n";
        $zag = "------------" . $un . "\nContent-Type:text/html;\n";
        $zag .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";

        if (is_array($filename)) {

            foreach ($filename as $k => $file) {

                $f = fopen($file, "rb");

                $zag .= "------------" . $un
                        . PHP_EOL
                        . 'Content-Type: application/octet-stream;'
                        . 'name="' . basename($file) . '"'
                        . PHP_EOL
                        . 'Content-Transfer-Encoding:base64'
                        . PHP_EOL
                        . 'Content-Disposition:attachment;'
                        . 'filename="' . basename($file) . '"'
                        . PHP_EOL . PHP_EOL
                        . chunk_split(base64_encode(fread($f, filesize($file)))) . "\n";
            }
        }

        //
        elseif (isset($filename{2}) && file_exists($filename)) {

            $f = fopen($filename, "rb");

            $zag .= '------------' . $un
                    . PHP_EOL
                    . 'Content-Type: application/octet-stream;'
                    . 'name="' . basename($filename) . '"'
                    . PHP_EOL
                    . 'Content-Transfer-Encoding:base64'
                    . PHP_EOL
                    . 'Content-Disposition:attachment;'
                    . 'filename="' . basename($filename) . '"'
                    . PHP_EOL
                    . PHP_EOL
                    . chunk_split(base64_encode(fread($f, filesize($filename))))
                    . PHP_EOL;
        }

        return @mail("$to", "$subj", $zag, $head);
    }

    /**
     * отправка почты через сенд майл и если там не получилось через mail()
     * @global type $status
     * @param type $head
     * @param type $tpl
     * @param type $dop
     * @return boolean
     */
    public static function sendNow($db, $from, $to, $head = 'Сообщение', $tpl = 'default', $dop = null) {

        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
            global $status;

        $send = false;

        $dop['head'] = $head;

        //require_once ($_SERVER['DOCUMENT_ROOT'] . '/include/f/mail.php');
        // шлём через сенд пульс
        if (isset(self::$sendpulse_id{5}) && isset(self::$sendpulse_key{5})) {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= 'Есть коды для сенд пульс, шлём тут<br/>';

            $dop['kolvo_send_hour'] = self::colvoMailSendpulse($db, $dop['folder']);

            // $dop['limit_sendpulse_on_day'] = round(trim($dop['limit_sendpulse_on_day']));
            $dop['limit_sendpulse_on_day'] = ( ( isset($dop['limit_sendpulse_on_day']{0}) && is_numeric($dop['limit_sendpulse_on_day']) ) ? $dop['limit_sendpulse_on_day'] : 11 );

            if ($dop['kolvo_send_hour'] <= $dop['limit_sendpulse_on_day']) {
                // $dop['sendsend'] = 123 . ' ' . $dop['kolvo_send_hour'] .' <= '. $dop['limit_sendpulse_on_day'];
                $send = self::sendMailSendpulse($db, $from, $to, $tpl, $head, $dop);
            }
        }

        // если отправили серез сенд пульс
        if ($send === true) {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $dop['stat'] = $status;

            return f\end2('Письмо отправлено c 1 попытки', 'ok', array_merge($dop, array('file' => __FILE__, 'line' => __LINE__)), 'array');
        }

        // если не послали через сенд пульс, шлём обычным методом
        else {

            // require_once ($_SERVER['DOCUMENT_ROOT'] . '/include/f/ajax.php');

            self::$ns['from'] = $from;
            self::$ns['to'] = $to;

            /*
              if( $tpl == 'nexit_myshop' && isset($dop['text']) ){
              $dop['head'] = $dop['head'];
              $dop['text'] = $dop['text'];
              }
             */

            if (strpos($tpl, 'smarty') && file_exists($_SERVER['DOCUMENT_ROOT'] . DS . 'template-mail' . DS . $tpl . '.htm')) {
                $dop['stat_mail_send'] = __LINE__;
                //$vars['text'] = $dop['text'];
                // require_once( $_SERVER['DOCUMENT_ROOT'] . '/include/f/smarty.php' );
                require_once( $_SERVER['DOCUMENT_ROOT']
                        . DS . 'vendor'
                        . DS . 'didrive_mod'
                        . DS . 'myshop'
                        . DS . '2'
                        . DS . 'smarty.php' ); // название файла библиотеки

                self::$body = f\compileSmarty($tpl . '.htm', $dop, $_SERVER['DOCUMENT_ROOT'] . DS . 'template-mail');
            }
            //
            elseif ($tpl === null && isset(self::$body{50})) {

                $dop['stat_mail_send'] = __LINE__;
            }
            //
            else {
                $dop['stat_mail_send'] = __LINE__;
                self::$body = f\getTemplateMail($tpl, $dop);
            }

            // echo '<hr><hr>'.'<hr>'.'<hr>'.$rr.'<hr>'.'<hr>'.'<hr>'.'<hr>';
            self::ns_send($head, self::$body, ( isset($dop['from_domain']{2}) ? $dop['from_domain'] : $_SERVER['HTTP_HOST']));

            $dop['file'] = __FILE__;
            $dop['line'] = __LINE__;

            return f\end2('Письмо отправлено обычным 0 обработчиком', 'ok', $dop, 'array');
        }

        $status .= '<table><tr><td width="15" bgcolor="#2B75D0" align="center" nowrap style="direction: ltr; writing-mode:  tb-rl;"><strong><a href="' . __FILE__ . '" title="' . __FILE__ . ' [' . __LINE__ . ']">emailer.sendNow</a></strong></td><td bgcolor="#D5E2F7">';

        $status .= '<abbr title="' . __FILE__ . ' [' . __LINE__ . ']"> class emailer | в основе сообщения шаблон <u>' . $tpl . '</u></abbr><br>';

        // Escape all quotes, else the eval will fail.
        self::$msg = str_replace("'", "\'", self::$msg);
        self::$msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", self::$msg);

        // We now try and pull a subject from the email body ... if it exists,
        // do this here because the subject may contain a variable
        $drop_header = '';
        $match = array();

        if (preg_match('#^(Subject:(.*?))$#m', self::$msg, $match)) {
            self::$subject = (trim($match[2]) != '') ? trim($match[2]) : ((self::$subject != '') ? self::$subject : 'No Subject');
            $drop_header .= '[\r\n]*?' . phpbb_preg_quote($match[1], '#');
        } else {
            self::$subject = ((self::$subject != '') ? self::$subject : 'No Subject');
        }

        $status .= '| class emailer [' . __LINE__ . ']| тема - <strong><u>' . self::$subject . '</u></strong><br>';

        if ($drop_header != '') {
            self::$msg = trim(preg_replace('#' . $drop_header . '#s', '', self::$msg));
        }

        $to = self::$addresses['to'];
        $cc = (count(self::$addresses['cc'])) ? implode(', ', self::$addresses['cc']) : '';
        $bcc = (count(self::$addresses['bcc'])) ? implode(', ', self::$addresses['bcc']) : '';

        // Build header
        self::$extra_headers = ((self::$reply_to != '') ? "Reply-to: self::$reply_to\n" : '')
                . ( (self::$from != '') ? 'From: ' . self::$from . PHP_EOL : 'From: ' . $my["mail_support"] . PHP_EOL )
                . "Return-Path: " . $my["mail_support"] . PHP_EOL
                . 'Message-ID: <' . md5(uniqid(time())) . '@' . $board_config['server_name'] . '>' . PHP_EOL
                . 'MIME-Version: 1.0' . PHP_EOL
                . 'Content-type: text/plain; charset=UTF-8' . PHP_EOL
                . 'Content-transfer-encoding: 8bit' . PHP_EOL
                . 'Date: ' . date('r', time()) . PHP_EOL
                . 'X-Priority: 3' . PHP_EOL
                . 'X-MSMail-Priority: Normal' . PHP_EOL
                . 'X-Mailer: system NYOS' . PHP_EOL
                . 'X-MimeOLE: Produced by system NYOS' . PHP_EOL
                . self::$extra_headers . (($cc != '') ? "Cc: $cc\n" : '')
                . (($bcc != '') ? "Bcc: $bcc\n" : '')
        ;

        $empty_to_header = ($to == '') ? TRUE : FALSE;

        $to = ($to == '') ? (($board_config['sendmail_fix']) ? ' ' : 'Undisclosed-recipients:;') : $to;

        $result = @mail($to, self::$subject, preg_replace("#(?<!\r)\n#s", PHP_EOL, self::$msg), self::$extra_headers);

        if (!$result && $empty_to_header) {
            $to = ' ';
            $result = @mail($to, self::$subject, preg_replace("#(?<!\r)\n#s", PHP_EOL, self::$msg), self::$extra_headers);

            if (!$result) {
                $status .= '[' . __LINE__ . '] email не отправлен даже со 2-ой попытки.<br>';
            }
        } else {
            $status .= '[' . __LINE__ . '] email удачно отправлен с 1-го раза.<br>';
        }

        $status .= '[' . __LINE__ . '] END return TRUE<br>';
        $status .= '</td></tr></table> ';
        return true;
    }

    /**
     * считаем сколько email отправлено за крайний час
     * @param type $db
     * @param type $folder
     * @return type
     */
    public static function colvoMailSendpulse($db, $folder) {

        //global $status;
        $status = '';
        $sql = $db->sql_query('SELECT `t`, COUNT(`id`) as `kolvo` FROM `gm_mail`
            WHERE
                `folder` = \'' . addslashes($folder) . '\'
                AND EXTRACT(HOUR FROM `t`) = \'' . date('h', $_SERVER['REQUEST_TIME']) . '\'
            GROUP BY
                EXTRACT(HOUR FROM `t`)
            ORDER BY
                EXTRACT(HOUR FROM `t`) DESC
            LIMIT 1
            ;');
        //echo $status;
        // while( $e = $db->sql_fr($sql) ){ f\pa($e); }
        $e = $db->sql_fr($sql);
        //return $db->sql_numrows($sql);
        return $e['kolvo'];
    }

    /**
     * посыл письма через сендпульс шаблна смарти
     * @param type $sp_id
     * @param type $sp_secret
     * @param type $from
     * @param type $to
     * @param type $tpl
     * @param type $head
     * @param type $vars
     * @return boolean
     */
    public static function sendMailSendpulse($db, $from, $to, $tpl = 'default', $head = null, $vars = array()) {

        //global $status;
        // echo '<pre>'; print_r( $vars ); echo '</pre>';
        // https://login.sendpulse.com/settings/#api

        define('API_USER_ID', self::$sendpulse_id);
        define('API_SECRET', self::$sendpulse_key);

        // define('TOKEN_STORAGE', 'file');
        // $SPApiProxy = new SendpulseApi(API_USER_ID, API_SECRET, TOKEN_STORAGE);

        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/ApiInterface.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/ApiClient.php' );

        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/Storage/TokenStorageInterface.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/Storage/FileStorage.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/Storage/MemcacheStorage.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/Storage/MemcachedStorage.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/class/sendpulse/Storage/SessionStorage.php' );

        $SPApiClient = new Sendpulse\RestApi\ApiClient(API_USER_ID, API_SECRET, new Sendpulse\RestApi\storage\SessionStorage());

        if (strpos($tpl, 'smarty') && file_exists($_SERVER['DOCUMENT_ROOT'] . DS . 'template-mail' . DS . $tpl)) {

            require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/f/smarty.php' );
            self::$body = f\compileSmarty($tpl, $vars, $_SERVER['DOCUMENT_ROOT'] . DS . 'template-mail');
        } elseif ($tpl === null && isset(self::$body{50})) {
            
        } else {
            self::$body = f\getTemplateMail($tpl, $vars);
        }

        // Send mail using SMTP
        $email = array(
            'html' => self::$body,
            'subject' => $head
        );
        $email['text'] = isset($vars['text']{2}) ? $vars['text'] : strip_tags($email['html']);


        //,
//                'answer_to' => array(
//                    'name' => 'Управление гос. экспертизы проектной документации',
//                    'email' => 'support@expertiza72.ru'
//                ),
//                'bcc' => array(
//                    array(
//                        'name' => 'Тех. поддержка',
//                        'email' => 'support@uralweb.info'
//                    )
//                )
        //,
        // 'attachments' => array(
        // 'file.txt' => file_get_contents(PATH_TO_ATTACH_FILE)
        // )



        if (isset($from['email']{1})) {
            $email['from'] = array(
                'name' => $from['name'],
                'email' => $from['email']
            );
        } else {
            $email['from'] = array(
                'name' => $from,
                'email' => $from
            );
        }

        // $to['email'] = 'domape@rambler.ru';

        $email['to'] = array(array('email' => $to));
        /*
          if( isset( $to['email']{1}) ){
          $email['to'] = array(
          array(
          'name' => $to['name'],
          'email' => $to['email']
          )
          );
          }else{
          $email['to'] = array(
          array(
          'name' => '',
          'email' => $to
          )
          );
          }
         */

        //f\pa($email);

        $ee = $SPApiClient->smtpSendMail($email);
        $ee2 = f\object_to_array($ee);

        // f\pa($email);
        //f\pa($ee2);
        // echo '<hr>';
        // подготовка массива для записи в бд
        $email2 = $email;
        //$email2['result'] = f\object_to_array($ee);
        // $email2['result'] = json_encode($ee2);

        $email2 = array(
            'folder' => $vars['folder'],
            'domain' => $_SERVER['HTTP_HOST'],
            'from' => $email['from']['email'],
            'to' => $to,
            'head' => $head,
            'message' => $email['html'],
            'array_var' => serialize($ee2),
            'd' => 'NOW',
            't' => 'NOW'
        );
        //$email3 = array_merge($email2,$ee2);
        //f\pa( $email3 );
        // exit;

        $status = '';
        \f\db\db2_insert($db, 'gm_mail', $email2, 'da');
        //echo $status;

        if (isset($ee2['is_error']) && $ee2['is_error'] == true) {
            //if ( isset($ee->result) && $ee->result == true) {
            return $ee2;
        } else {
            return true;
        }
    }

}

// class emailer

// $emailer = new mailpost();
