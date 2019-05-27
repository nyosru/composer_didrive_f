<?php

namespace Nyos;

// строки безопасности

if (!defined('IN_NYOS_PROJECT'))
    die('<center><br><br><br><br><p>Сработала защита <b>c.NYOS</b> от злостных розовых хакеров.</p>
    <a href="http://www.uralweb.info" target="_blank">Создание, дизайн, вёрстка и программирование сайтов.</a><br />
    <a href="http://www.nyos.ru" target="_blank">Только отдельные услуги: Дизайн, вёрстка и программирование сайтов.</a>');

class Nyos_image {

    public static $mime = null; // тип обработанного файла
    public static $image = null; //идентификатор самого изображения
    public static $width = 0; //исходная ширина
    public static $height = 0; //исходная высота
    public static $type = ''; //тип изображения (jpg, png, gif)
    public static $status = array(); //статусные сообщения
    public static $show = false; //статусные сообщения

    /**
     * ответ
     */

    public static function o($text, $status = true) {
        $e = array('text' => $text, 'status' => $status);
        self::$status[] = $e;
        return $e;
    }

    /**
     * показ изображения через die()
     * @param type $file
     */
    public static function showImage(string $file = null, bool $die_after_show = true) {

// die( __FUNCTION__.' #'.__LINE__ );

        if (!file_exists($file))
            throw new \Exception('не нашли файл указанный как картинка ' . $file);

        header('Content-type: ' . mime_content_type($file));
        header('Cache-Control: public');
        header('Pragma: cache');
        $expires = 60 * 60 * 4;
        header('Expires: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + $expires) . 'GMT');

        if ($die_after_show === true) {
            die(file_get_contents($file));
        } else {
            file_get_contents($file);
        }
    }

    public static function showImageInMemory(string $save_to_file = null, bool $delete_img_memory = true) {

        if (empty(self::$image))
            throw new \Exception('нет оцифрованного рисунка');

        if (empty(self::$mime))
            throw new \Exception('не определён тип изображения');

        header('Content-type: ' . self::$mime);
// Выводим изображение

        if (self::$mime == 'image/png') {

//            $background = imagecolorallocatealpha(self::$image, 255, 255, 255, 127);
//            imagecolortransparent(self::$image, $background);
//            imagealphablending(self::$image, true);
            imagesavealpha(self::$image, true);

            if (isset($save_to_file{3}))
                imagepng(self::$image, $save_to_file);

            imagepng(self::$image);
        } elseif (self::$mime == 'image/gif') {

            if (isset($save_to_file{3}))
                imagegif(self::$image, $save_to_file);

            imagegif(self::$image);
        } else {

            if (isset($save_to_file{3}))
                imagejpeg(self::$image, $save_to_file);

            imagejpeg(self::$image);
        }

// Освобождаем память
        if ($delete_img_memory === true)
            imagedestroy(self::$image);
    }

    public static function new_image($file) {

        if (!file_exists($file))
            return self::o('нет файла', false);

        $tt = self::readType2($file);

        if ($tt['status'] === false)
            return self::o('файл не картинка', false);

        self::openImage($file);
        self::getSize();
    }

    /**
     * читаем изображение
     * @param string $file
     * @throws \Exception
     */
    public static function readImage(string $file) {


        if (!file_exists($file))
            throw new \Exception('нет файла');

        self::openImage($file);

        self::getSize();
    }

    /**
     * получаем размеры изображения в памяти
     */
    private static function getSize() {
        self::$width = imagesx(self::$image);
        self::$height = imagesy(self::$image);
    }

    private static function readType($file) {

        $mime = mime_content_type($file);

        if ($mime == 'image/jpeg') {
            self::$type = "jpg";
            return self::o('тип файла jpg');
        } elseif ($mime == 'image/png') {
            self::$type = "png";
            return self::o('тип файла png');
        } elseif ($mime == 'image/gif') {
            self::$type = "gif";
            return self::o('тип файла gif');
        } else {
            return self::o('тип файла непонятный', false);
        }
    }

    /**
     * 
     * @param type $to_file
     * @param string $out_type
     * jpg png gif
     * @param bool $rewrite
     * @param int $quality
     * 0-100 (default 80)
     * @return type
     * @throws \Exception
     */
    public static function saveImage($to_file, string $out_type = null, bool $rewrite = false, int $quality = 90) {

        if ($rewrite === false && file_exists($to_file))
            return true;

        if ($quality < 0 && $quality > 100)
            throw new \Exception('качество указано неверно');

        if ($out_type === false) {
            if (self::$type == 'jpg') {
                imagejpeg(self::$image, $to_file, $quality);
                return self::o('файл сохранён (' . self::$type . ')', true);
            }
//
            elseif (self::$type == 'png') {
                imagepng(self::$image, $to_file);
                return self::o('файл сохранён (' . self::$type . ')', true);
            }
//
            elseif (self::$type == 'gif') {
                imagegif(self::$image, $to_file);
                return self::o('файл сохранён (' . self::$type . ')', true);
            }
//
            else {
                return self::o('непонятный тип файла (' . __FILE__ . '|#' . __LINE__ . ')', false);
            }
        } elseif ($out_type == 'jpg') {
            imagejpeg(self::$image, $to_file, $quality);
            return self::o('файл сохранён (' . $out_type . ')', true);
        } elseif ($out_type == 'png') {
            imagepng(self::$image, $to_file);
            return self::o('файл сохранён (' . $out_type . ')', true);
        } elseif ($out_type == 'gif') {
            imagegif(self::$image, $to_file);
            return self::o('файл сохранён (' . $out_type . ')', true);
        } else {
            return self::o('непонятный тип файла (' . __FILE__ . '|#' . __LINE__ . ')', false);
        }
    }

    public static function blank($db, $shop, $domain) {

// $show_status = true;

        if (isset($show_status) && $show_status === true) {
            $status = '';
            $_SESSION['status1'] = true;
        }

        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            global $status;

            if (isset($show_status) && $show_status === true)
                $status = '';

            $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
        }

        if (isset($shop) && is_numeric($shop)) {
            
        } else {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= 'указан не верно пользователь<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

            return f\end2('Ошибка в указании номера магазина', false, array(), 'array');
        }


        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

            if (isset($show_status) && $show_status === true)
                echo $status;
        }

        return f\end3($res['summa'], true);
    }

    /**
     * поворот jpg фотки если нужно по exif данным 
     * @global string $status
     * @param type $origin
     * оригинальный файл
     * @param type $result
     * куда записать результат
     * @param type $del_origin
     * удалить оригинал true
     * или нет false
     * @return type
     */
    public static function autoJpgRotate($origin, $result = null, $quality = 80, $del_origin = true) {

// Прочитать данные EXIF
        $exif = @exif_read_data($origin);

        if (isset($exif['MimeType']) && $exif['MimeType'] == 'image/jpeg') {

            $image = imagecreatefromjpeg($origin);

// Поворот на 180 градусов
            if (isset($exif['Orientation']) && $exif['Orientation'] == 3) {
                $image = imagerotate($image, 180, 0);
            }
// Поворот вправо на 90 градусов
            elseif (isset($exif['Orientation']) && $exif['Orientation'] == 6) {
                $image = imagerotate($image, -90, 0);
            }
// Поворот влево на 90 градусов
            elseif (isset($exif['Orientation']) && $exif['Orientation'] == 8) {

                $image = imagerotate($image, 90, 0);
            }

// imagejpeg($image);

            if ($result !== null && $origin != $result) {
                imagejpeg($image, $result, $quality);
            } else {
                imagejpeg($image, $origin, $quality);
            }

            return \f\end3('файл изменён, сохранён', true);
        }
// если не jpg просто копируем
        else {

            if ($result !== null) {
                copy($origin, $result);

                if ($del_origin === true && $origin != $result && file_exists($origin))
                    unlink($origin);
            }

            return \f\end3('файл не того формата, скопирован', true);
        }
    }

    /**
     * расчёт размеров для вставки изображения
     * @global string $status
     * @param type $now_w
     * @param type $now_h
     * @param type $new_w
     * @param type $new_h
     * @param type $fill
     * true - заливка всей рамки без пропусков
     * false - расчёт размеров вставить в рамку без обрези
     * @return type
     */
    public static function calcNewRazmer($now_w, $now_h, $new_w, $new_h, $fill = false) {

// $show_status = true;

        if (isset($show_status) && $show_status === true) {
            $status = '';
            $_SESSION['status1'] = true;
        }

        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            global $status;

            if (isset($show_status) && $show_status === true)
                $status = '';

            $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
        }

        if (isset($now_w) && isset($new_w)) {
            
        } else {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= 'один из размеров не цифра <span class="bot_line">#' . __LINE__ . '</span></fieldset>';

            return f\end2('один из размеров не цифра', false, array(), 'array');
        }


        $dop = array('opis' => '');
// если указали и всоту
        if (is_numeric($now_h) && is_numeric($new_h)) {

            $dop['2cx'] = $coef_x = ceil($new_h / ($now_h / 100));
            $dop['2cy'] = $coef_y = ceil($new_w / ($now_w / 100));
            $dop['3cx'] = $coef_x = ceil($now_h / ($new_h / 100));
            $dop['3cy'] = $coef_y = ceil($now_w / ($new_w / 100));

            $dop['pic_w'] = $pic_w = $now_w;
            $dop['pic_h'] = $pic_h = $now_h;

            $dop['pole_w'] = $pole_w = $new_w;
            $dop['pole_h'] = $pole_h = $new_h;

// если растягиваем изображение по рамке с отрезками лишнего (полная заливка)
            if ($fill === true) {

                $dop['opis'] .= '<br/>ширина больше высоты результата';

                $dop['opis'] .= '<br/>исходник ширина больше высоты результата';
                $dop['opis'] .= '<br/>результат ' . $new_w . '*' . $new_h . ' и исходник ' . $now_w . '*' . $now_h;

                $dop['new_img_w'] = $new_w;
                $dop['new_img_h'] = $now_h / 100 * ( $new_w / ($now_w / 100) );

                $dop['opis'] .= '<br/>новая картинка ' . $new_img_w . '*' . $new_img_h;

                if ($dop['new_img_h'] > $new_h) {

                    $dop['postiotion']['y0'] = ($dop['new_img_h'] - $new_h) / 2 * -1;
                    $dop['postiotion']['y'] = ($dop['new_img_h'] - $new_h) / 2 + $dop['new_img_h'];
                } elseif ($dop['new_img_h'] < $new_h) {

                    $dop['postiotion']['y0'] = ($new_h - $dop['new_img_h']) / 2;
                    $dop['postiotion']['y'] = $dop['new_img_h'];
                } else {

                    $dop['postiotion']['y0'] = 0;
                    $dop['postiotion']['y'] = $new_h;
                }

                $dop['postiotion']['x0'] = 0;
                $dop['postiotion']['x'] = $new_w;
            }

// если встраиваем изображение в рамку
            else {


                $dop['opis'] .= '<br/>встраиваем картинку';
                $dop['opis'] .= '<br/>ширина больше высоты результата';

                $dop['opis'] .= '<br/>исходник ширина больше высоты результата';
                $dop['opis'] .= '<br/>результат ' . $new_w . '*' . $new_h . ' и исходник ' . $now_w . '*' . $now_h;


                if ($pole_w >= $pic_w) {

                    if ($pole_h >= $pic_h) {

                        $c = $dop['postiotion']['c'] = $pole_h / ($pic_h / 100);
// $c = ceil($new_h/100/($now_h/100));

                        if ($c <= 100) {
                            $new_pic_w = $pic_w / 100 * $c;
                            $new_pic_h = $pic_h / 100 * $c;
                        } else {
                            $new_pic_w = $pic_w;
                            $new_pic_h = $pic_h;
                        }

                        $dop['postiotion']['x0'] = ceil($pole_w / 2 - $new_pic_w / 2);
                        $dop['postiotion']['x'] = ceil($new_pic_w);

                        $dop['postiotion']['y0'] = ceil(($pole_h - $new_pic_h) / 2);
                        $dop['postiotion']['y'] = $new_pic_h;
                    } else {

                        $dop['line'] = __LINE__;

                        $c = $dop['postiotion']['c'] = $pole_h / ($pic_h / 100);
// $c = ceil($new_h/100/($now_h/100));

                        if ($c <= 100) {
                            $new_pic_w = $pic_w / 100 * $c;
                            $new_pic_h = $pic_h / 100 * $c;
                        } else {
                            $new_pic_w = $pic_w;
                            $new_pic_h = $pic_h;
                        }

                        $dop['postiotion']['x0'] = ceil($pole_w / 2 - $new_pic_w / 2);
                        $dop['postiotion']['x'] = ceil($new_pic_w);

                        $dop['postiotion']['y0'] = ceil(($pole_h - $new_pic_h) / 2);
                        $dop['postiotion']['y'] = $new_pic_h;
                    }
                } else {

                    if ($pole_h >= $pic_h) {

                        $c = $dop['postiotion']['c'] = $pole_w / ($pic_w / 100);
// $c = ceil($new_h/100/($now_h/100));

                        if ($c <= 100) {
                            $new_pic_w = $pic_w / 100 * $c;
                            $new_pic_h = $pic_h / 100 * $c;
                        } else {
                            $new_pic_w = $pic_w;
                            $new_pic_h = $pic_h;
                        }

                        $dop['postiotion']['x0'] = ceil($pole_w / 2 - $new_pic_w / 2);
                        $dop['postiotion']['x'] = ceil($new_pic_w);

                        $dop['postiotion']['y0'] = ceil(($pole_h - $new_pic_h) / 2);
                        $dop['postiotion']['y'] = $new_pic_h;
                    } else {

                        $dop['line'] = __LINE__;

                        $c = $dop['postiotion']['c'] = $pole_w / ($pic_w / 100);
// $c = ceil($new_h/100/($now_h/100));

                        if ($c <= 100) {
                            $new_pic_w = $pic_w / 100 * $c;
                            $new_pic_h = $pic_h / 100 * $c;
                        } else {
                            $new_pic_w = $pic_w;
                            $new_pic_h = $pic_h;
                        }

                        $dop['postiotion']['x0'] = ceil($pole_w / 2 - $new_pic_w / 2);
                        $dop['postiotion']['x'] = ceil($new_pic_w);

                        $dop['postiotion']['y0'] = ceil(($pole_h - $new_pic_h) / 2);
                        $dop['postiotion']['y'] = $new_pic_h;
                    }
                }





                if (1 == 2) {


                    if ($dop['2cx'] > $dop['2cy']) {
                        
                    } else {

                        if ($dop['3cx'] > $dop['3cy']) {



// $dop['new_img_w'] = $new_w-$now_w-($now_w/2);

                            $dop['postiotion']['x0'] = $new_w - $now_w - ($now_w / 2);
                            $dop['postiotion']['x'] = $dop['postiotion']['x0'] + $now_w;

                            $dop['postiotion']['x0'] = 100;
                            $dop['postiotion']['x'] = 500;

                            $dop['postiotion']['y0'] = 10;
                            $dop['postiotion']['y'] = $new_h;
                            $dop['postiotion']['y'] = 300;





//$dop['postiotion']['y'] = $dop['postiotion']['y0']+ $new_h;
//                        $dop['postiotion']['y0'] = 0;
//                        $dop['postiotion']['y'] = 200;
                        }
//
                        else {

                            $dop['new_img_w'] = $new_w;
                            $dop['new_img_h'] = $now_h / 100 * ( $new_w / ($now_w / 100) );

                            $dop['opis'] .= '<br/>новая картинка ' . $new_img_w . '*' . $new_img_h;

                            $dop['postiotion']['y0'] = 0;
                            $dop['postiotion']['y'] = 300;


                            /*
                              if ($dop['new_img_h'] > $new_h) {

                              $dop['postiotion']['y0'] = ($dop['new_img_h'] - $new_h) / 2 * -1;
                              $dop['postiotion']['y'] = ($dop['new_img_h'] - $new_h) / 2 + $dop['new_img_h'];
                              } elseif ($dop['new_img_h'] < $new_h) {

                              $dop['postiotion']['y0'] = ($new_h - $dop['new_img_h']) / 2;
                              $dop['postiotion']['y'] = $dop['new_img_h'];
                              } else {

                              $dop['postiotion']['y0'] = 0;
                              $dop['postiotion']['y'] = $new_h;
                              }
                             */
                            $dop['postiotion']['x0'] = 0;
                            $dop['postiotion']['x'] = $new_w;
                        }
                    }
                }
            }
        }
// если указали только ширину
        else {



            $dop['opis'] .= '<br/>встраиваем картинку';
            $dop['opis'] .= '<br/>ширина больше высоты результата';

            $dop['opis'] .= '<br/>исходник ширина больше высоты результата';
            $dop['opis'] .= '<br/>результат ' . $new_w . '*' . $new_h . ' и исходник ' . $now_w . '*' . $now_h;

            $dop['coef2w'] = ceil($new_pic_w / ($pole_w / 100));

            $dop['postiotion']['x0'] = 0;
            $dop['postiotion']['x'] = ceil($pic_h / 100 * $dop['coef2w']);

            $dop['postiotion']['y0'] = 0;
            $dop['postiotion']['y'] = $new_pic_h;
        }





        if (1 == 2) {

            if ($now_w <= $now_h) {

                /*
                  // если коэфициент ширины больше (то вставляем по высоте и ровняем по ширине)
                  if ($coef_x >= $coef_y) {

                  $prop = $now_h / $new_h;

                  $dop['scr_x'] = ceil(($now_h - $new_w) / $prop / 2);
                  $dop['scr_y'] = 0;
                  //$dop['scr_y'] = $now_h;
                  $dop['scr_w'] = ceil($new_w * $prop);
                  $dop['scr_h'] = $now_h;
                  }
                  // если коэфициент высоты больше (то вставляем по ширине и ровняем по высоте )
                  else {

                  $prop = $now_w / $new_w;

                  $dop['scr_x'] = 0;
                  $dop['scr_y2'] = ceil( $now_h * $prop );
                  $dop['scr_y'] = 50;
                  $dop['scr_w'] = ceil($new_w * $prop);
                  $dop['scr_h'] = $new_h;
                  $dop['scr_h'] = 150;
                  }

                  if (1 == 2) {
                 * 
                 */
// echo '<br/>';
// echo 'щирина меньше высоты';

                if ($coef_x < $coef_y) {

// если картинка по периметру меньше 
                    if ($now_w < $new_w && $now_h > $new_h) {

                        $dop['line'] = __LINE__;

                        $dop['scr_x'] = 0;
                        $dop['scr_w'] = $now_w;
//$dop['new_w'] = $new_w;

                        $prop = $new_w / $now_w;
//$dop['prop'] = $prop;

                        $dop['scr_y'] = ($now_h * $prop - $new_h) / $prop / 2;
                        $dop['scr_h'] = $now_h / $prop;
                    } else {

                        $dop['line'] = __LINE__;

                        $prop = $now_w / $new_w;

                        $dop['scr_x'] = 0;
                        $dop['scr_y'] = ceil(($now_w - $new_h) / $prop / 2);
                        $dop['scr_w'] = $now_w;
                        $dop['scr_h'] = ceil($new_h * $prop);
                    }
                } else {





                    /*
                      $dop['scr_y'] = ceil(($now_h - $new_h) / $prop / 2);
                      $dop['scr_h'] = ceil( $new_h * $prop);
                     */

                    /*
                      $dop['ss1'] = '';
                      $dop['now_h'] = $now_h;

                      $dop['new_h'] = $now_h / 100 * $coef_y;
                      $dop['baza_h'] = $new_h ;
                      $dop['ostatok_h'] = ceil(($dop['new_h']-$new_h)/2) ;
                      $dop['ostatok_origin_h'] = $dop['ostatok_h']/100*(100-$coef_y);

                      $dop['new_w'] = $now_w / 100 * $coef_y;
                      $dop['ss2'] = '';
                     */

//$dop['scr_h'] = ceil($now_h * $prop);



                    /*
                      $dop['now_h_w'] = $now_h . '*' . $now_w;
                      $dop['new_h_w'] = $new_h . '*' . $new_w;

                      $coef_x = $new_h / ($now_h / 100);
                      $coef_y = $new_w / ($now_w / 100);
                      $dop['coef_h_w'] = $coef_x . '*' . $coef_y;
                      $dop['new_xx'] = ($now_h / 100) * $coef_x;
                      $dop['new_xy'] = ($now_w / 100) * $coef_x;
                      $dop['new_yx'] = ($now_h / 100) * $coef_y;
                      $dop['new_yy'] = ($now_w / 100) * $coef_y;
                     */
                }
            } else {

// если коэфициент ширины больше (то вставляем по высоте и ровняем по ширине)
                if ($coef_x >= $coef_y) {

                    $dop['line'] = __LINE__;

                    $prop = $now_h / $new_h;

                    $dop['scr_x'] = ceil(($now_h - $new_w) / $prop / 2);
                    $dop['scr_y'] = 0;
//$dop['scr_y'] = $now_h;
                    $dop['scr_w'] = ceil($new_w * $prop);
                    $dop['scr_h'] = $now_h;
                }
// если коэфициент высоты больше (то вставляем по ширине и ровняем по высоте )
                else {

                    $dop['line'] = __LINE__;

                    $prop = $now_w / $new_w;

                    $dop['scr_x'] = 0;
                    $dop['scr_y'] = ceil(($now_w - $new_h) / $prop / 2);
// $dop['scr_y'] = 0;
                    $dop['scr_w'] = $now_w;
                    $dop['scr_h'] = ceil($new_h * $prop);
                }
            }

//echo '<br/>';
//echo 'щирина больше высоты';
        }

//$dop['line'] = __LINE__;

        $dop['now_w_h'] = $now_w . '*' . $now_h;
        $dop['new_w_h'] = $new_w . '*' . $new_h;

        $coef_x = ceil($new_h / ($now_h / 100));
        $coef_y = ceil($new_w / ($now_w / 100));

        $dop['coef_h_w'] = $coef_x . '*' . $coef_y;

        $dop['new_x_w_h'] = ($now_w / 100) * $coef_x . ' + ' . ($now_h / 100) * $coef_x;
//                $dop['new_xx'] = ($now_h/100)*$coef_x;
//                $dop['new_xy'] = ($now_w/100)*$coef_x;
        $dop['new_y_w_h'] = ($now_w / 100) * $coef_y . ' + ' . ($now_h / 100) * $coef_y;
//                $dop['new_yx'] = ($now_h/100)*$coef_y;
//                $dop['new_yy'] = ($now_w/100)*$coef_y;




        if (1 == 2) {



            if (( $new_w + $new_h) > ( $now_w + $now_h )) {

//            echo '<Br/>цель больше исходника';
            }

// периметр цели меньше периметра исходника
            else {

//            echo '<fieldset><legend>цель меньше исходника</legend>';

                if ($now_w < $now_h) {




//            echo '<fieldset><legend>ширина меньше высоты</legend>';
//
//                echo '<Br/>'
//                . '<Br/>'
//                . 'новые размеры';
//
//                echo '<Br/>'
//                    . '<Br/>';
//
//                echo '<Br/>'
//                    .'старая ширина '.$now_w;
//                echo '<Br/>'
//                    .'новая ширина '.$new_w;

                    $dop['w'] = $new_w;

                    $coef_w = $new_w / ( $now_w / 100 );
//                
//                echo '<Br/>'
//                    .'коэфициент '.$coef_w;

                    $new_h2 = ceil(( $now_h / 100 ) * ( 100 - $coef_w ));
// $new_h2 = ceil( ( $new_h / 100 ) * ( 100-$coef_w )% );
//
//                echo '<Br/>'
//                    .'старая высота '.$now_h;
//                echo '<Br/>'
//                    .'новая высота '.$new_h2;

                    $dop['y-padding'] = ceil(( $now_h - $new_h2 ) / 2);

//                echo '<Br/>'
//                    .'отступ от верха '.$dop['y-padding'];
////                
//                echo '</fieldset>';
                } elseif ($now_w > $now_h) {

//echo '111111';
//            echo '<fieldset><legend>ширина меньше высоты</legend>';
//
//                echo '<Br/>'
//                . '<Br/>'
//                . 'новые размеры';
//
//                echo '<Br/>'
//                    . '<Br/>';
//
//                echo '<Br/>'
//                    .'старая ширина '.$now_w;
//                echo '<Br/>'
//                    .'новая ширина '.$new_w;

                    $dop['w'] = $new_w;

                    $coef_w = $new_w / ( $now_w / 100 );
//                
//                echo '<Br/>'
//                    .'коэфициент '.$coef_w;

                    $new_h2 = ceil(( $now_h / 100 ) * ( 100 - $coef_w ));
// $new_h2 = ceil( ( $new_h / 100 ) * ( 100-$coef_w )% );
//
//                echo '<Br/>'
//                    .'старая высота '.$now_h;
//                echo '<Br/>'
//                    .'новая высота '.$new_h2;

                    $dop['y-padding'] = ceil(( $new_h2 - $new_h ) / 2);

//                echo '<Br/>'
//                    .'отступ от верха '.$dop['y-padding'];
////                
//                echo '</fieldset>';
                }

//            echo '</fieldset>';
            }
            /*
              echo '<br/>';

              if (self::$width > self::$height) {

              echo '<Br/>Ширина больше высоты (горизонтальная картинка)';

              $xx = ceil(( self::$width - self::$height ) / 2);
              $yy = ceil(self::$height);

              $x1 = $xx;
              $y1 = 0;

              $x2 = self::$height;
              $y2 = self::$height;
              }
              //
              elseif (self::$width < self::$height) {

              echo '<Br/>Ширина меньше высоты (вертикальная картинка)';

              $xx = ceil(self::$width);
              $yy = ceil(( self::$height - self::$width ) / 2);

              $x1 = 0;
              $y1 = $yy;

              $x2 = $xx;
              $y2 = $xx;

              // если по ширине
              //if( $w < self::$width )
              }
              else {

              $x1 = 0;
              $y1 = 0;
              $x2 = self::$width;
              $y2 = self::$height;
              }
             */








//if( !function_exists(f\end3) )
//  require_once $_SERVER['DOCUMENT_ROOT'].DS.'0.all'.DS.'f'.DS.'ajax.php';
        }



        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

            if (isset($show_status) && $show_status === true)
                echo $status;
        }

        return \f\end3('ага новые размеры', true, $dop);
    }

    /**
     * режем картинку до указанной длинны границы
     * @param type $w
     */
    public static function creatThumbnailKvadrat($w) {

        $new_image = imagecreatetruecolor($w, $w);

        if (self::$width > self::$height) {

            $xx = ceil(( self::$width - self::$height ) / 2);
            $yy = ceil(self::$height);

            $x1 = $xx;
            $y1 = 0;

            $x2 = self::$height;
            $y2 = self::$height;
        } elseif (self::$width < self::$height) {

            $xx = ceil(self::$width);
            $yy = ceil(( self::$height - self::$width ) / 2);

            $x1 = 0;
            $y1 = $yy;
            $x2 = $xx;
            $y2 = $xx;
        } else {

            $x1 = 0;
            $y1 = 0;
            $x2 = self::$width;
            $y2 = self::$height;
        }

        imagecopyresampled($new_image, self::$image, 0, 0, $x1, $y1, $w, $w, $x2, $y2);
//echo '<br/>imagecopyresampled( '.$new_image.', '.self::$image.', 0, 0, '.$x1.', '.$y1.', '.$w.', '.$w.', '.$x2.', '.$y2.' );';
        self::$image = $new_image;
        self::getSize();
    }

    /**
     * режем картинку до гирины, высота в пропорциях
     * @param string $img_origin картинка оригинал
     * @param int $w минимальная ширина
     * @param int $h минимальная высота (пока не подключено)
     * @expectedExceptionCode 10 Изображение больше чем цель
     */
    public static function creatThumbnailProporcii($img_origin, int $w = null, int $h = null) {

        /*
          // файл
          $filename = 'test.jpg';

          // задание максимальной ширины и высоты
          $width = 200;
          $height = 200;

          // тип содержимого
          header('Content-Type: image/jpeg');

          // получение новых размеров
          list($width_orig, $height_orig) = getimagesize($filename);

          $ratio_orig = $width_orig/$height_orig;

          if ($width/$height > $ratio_orig) {
          $width = $height*$ratio_orig;
          } else {
          $height = $width/$ratio_orig;
          }

          // ресэмплирование
          $image_p = imagecreatetruecolor($width, $height);
          $image = imagecreatefromjpeg($filename);
          imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

          // вывод
          imagejpeg($image_p, null, 100);
         */

        if ($w !== null) {

            $xx = ceil($w / (self::$width / 100));

            $x1 = 0;
            $x2 = $w;
            $y1 = 0;
            $y2 = ceil(self::$height / 100 * $xx);

            $new_image = imagecreatetruecolor($x2, $y2);
        } else {
            throw new \Exception('Не указана требуемая ширина избражения', 10);
        }

// imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, 0, 0, $x2, $y2);
// echo '<br/>imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, '.self::$width.', '.self::$height.', '.$x2.', '.$y2.'); ';
        imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, $x2, $y2, self::$width, self::$height);
//echo '<br/>imagecopyresampled( '.$new_image.', '.self::$image.', 0, 0, '.$x1.', '.$y1.', '.$w.', '.$w.', '.$x2.', '.$y2.' );';

        self::$image = $new_image;
        self::getSize();
    }

    /*
     * прочее
      function image_resize($src, $dst, $width, $height, $crop=0){

      if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

      $type = strtolower(substr(strrchr($src,"."),1));
      if($type == 'jpeg') $type = 'jpg';
      switch($type){
      case 'bmp': $img = imagecreatefromwbmp($src); break;
      case 'gif': $img = imagecreatefromgif($src); break;
      case 'jpg': $img = imagecreatefromjpeg($src); break;
      case 'png': $img = imagecreatefrompng($src); break;
      default : return "Unsupported picture type!";
      }

      // resize
      if($crop){
      if($w < $width or $h < $height) return "Picture is too small!";
      $ratio = max($width/$w, $height/$h);
      $h = $height / $ratio;
      $x = ($w - $width / $ratio) / 2;
      $w = $width / $ratio;
      }
      else{
      if($w < $width and $h < $height) return "Picture is too small!";
      $ratio = min($width/$w, $height/$h);
      $width = $w * $ratio;
      $height = $h * $ratio;
      $x = 0;
      }

      $new = imagecreatetruecolor($width, $height);

      // preserve transparency
      if($type == "gif" or $type == "png"){
      imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
      imagealphablending($new, false);
      imagesavealpha($new, true);
      }

      imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

      switch($type){
      case 'bmp': imagewbmp($new, $dst); break;
      case 'gif': imagegif($new, $dst); break;
      case 'jpg': imagejpeg($new, $dst); break;
      case 'png': imagepng($new, $dst); break;
      }
      return true;
      }
     */

    /**
     * создание картинки с нужной высотой и шириной ( уменьшение до минимального края, обрезка оставшегося, вставка в размер )
     * @param type $w
     * @param type $h
     */
    public static function creatThumbnailOut($w, $h) {


//die('123123');

        /*
          if( self::$width < $w || self::$height < $h ){
          $pr_w = $w/(self::$width/100);
          $pr_h = $h/(self::$height/100);

          echo $w .' '.self::$width.'<br/>'
          .'<br/>'. $h.' '.self::$height.'<br/>';
          echo '<br>';
          echo $pr_w.'*'.$pr_h;
          echo '<br>';
          echo 'w '.self::$width/100*$pr_w;
          echo '<br>';
          echo 'h '.self::$height/100*$pr_w;
          echo '<br>';
          echo '<br>';
          echo 'w '.self::$width/100*$pr_h;
          echo '<br>';
          echo 'h '.self::$height/100*$pr_h;
          die();

          }
         */

        $new_image = imagecreatetruecolor($w, $h);

//$red = imagecolorallocate($new_image, 255, 0, 0);
        $red2 = imagecolorallocate($new_image, 200, 200, 200);

        imagefill($new_image, 0, 0, $red2);

        $new = self::calcNewRazmer(self::$width, self::$height, $w, $h, false);

//        $new[data][postiotion] => Array
//            (
//                [y0] => -150
//                [y] => 810
//                [x0] => 0
//                [x] => 880
//            )        
//        if ( self::$show === true ) {
//            \f\pa($new);
//        }

        /*
          $str = '';
          if (isset($_GET['show_info'])) {
          foreach ($new['data'] as $k => $v) {
          $str .= $k . ':' . $v . PHP_EOL;
          }
          }
         */
//            $new[data][postiotion] => Array
//                (
//                    [y0] => -150
//                    [y] => 810
//                    [x0] => 0
//                    [x] => 880

        $w1 = $new['data']['postiotion']['x0'];
        $h1 = $new['data']['postiotion']['y0'];
        $w2 = $new['data']['postiotion']['x'];
        $h2 = $new['data']['postiotion']['y'];

//            $w1 = 10;
//            $h1 = 10;
//            $w2 = 150;
//            $h2 = 150;
// $pr_w = $new['data']['scr_x']/(self::$width/100);
// $new_h = $new['data']['scr_x']/100*$pr_w;
        /*
          $str = 'оригинал '.$new['data']['scr_x'].'x'.$new['data']['scr_y']
          .PHP_EOL.'новая высота '.$new_h;
         */
        imagecopyresampled(
                $new_image, self::$image, 0, 0, 0, 0, self::$width, self::$height, self::$width, self::$height
        );

        $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 20);

//imagefill($new_image, 0, 0, $transparent);
//imagefill($new_image, 0, 0, $red);

        imagefilledrectangle($new_image, 0, 0, $w, $h, $transparent);
//imagefilledrectangle( $new_image, 0, 0, 100, 100, $transparent );

        imagecopyresampled(
                $new_image, self::$image, $w1, $h1, 0, 0, $w2, $h2, self::$width, self::$height
        );

// ImageTTFtext($new_image, 14, 0, 20, 40, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $str);
//            if( $_SERVER['HTTP_HOST'] == 'xn--72-9kc6e.xn--p1ai' )
//            $dom = 'бу72.рф';

        if (( $w + $h ) > 600) {

            if (stripos($_SERVER['HTTP_HOST'], 'xn--') !== false) {

                if (!isset($Punycode))
                    $Punycode = new \TrueBV\Punycode();
//var_dump($Punycode->encode('renangonçalves.com')); // xn--renangonalves-pgb.com
                $dom = $Punycode->decode($_SERVER['HTTP_HOST']); // народнаяэкономика.рф
            } else {
                $dom = $_SERVER['HTTP_HOST'];
            }
//$dom = $_SERVER['HTTP_HOST'];

            $text_x = 20;
            $text_y = ceil($h - 20);

            $color = imagecolorallocate($new_image, 20, 20, 20);
            if (!isset($_GET['notext'])) {
                ImageTTFtext($new_image, 14, 0, $text_x, $text_y + 3, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $dom);
                ImageTTFtext($new_image, 14, 0, $text_x, $text_y - 3, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $dom);
                ImageTTFtext($new_image, 14, 0, $text_x - 3, $text_y, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $dom);
                ImageTTFtext($new_image, 14, 0, $text_x + 3, $text_y, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $dom);

                $color = imagecolorallocate($new_image, 250, 250, 250);

                ImageTTFtext($new_image, 14, 0, $text_x, $text_y, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $dom);
            }
        }

        /*
          if (isset($_GET['show_info']) || 1 == 2 ) {
          imagefilledrectangle($new_image, 10, 20, 350, 400, $red2);
          $color = ImageColorAllocate($new_image, 10, 10, 10);
          ImageTTFtext($new_image, 14, 0, 20, 40, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $str);
          }
         */
//echo '<br/>imagecopyresampled( '.$new_image.', '.self::$image.', 0, 0, '.$x1.', '.$y1.', '.$w.', '.$w.', '.$x2.', '.$y2.' );';

        self::$image = $new_image;
        self::getSize();
    }

    /**
     * создание картинки с высотой и шириной ( уменьшение до минимального края и обрезка оставшегося )
     * @param type $w
     * @param type $h
     */
    public static function creatThumbnail($w, $h) {

        /*
          if( self::$width < $w || self::$height < $h ){
          $pr_w = $w/(self::$width/100);
          $pr_h = $h/(self::$height/100);

          echo $w .' '.self::$width.'<br/>'
          .'<br/>'. $h.' '.self::$height.'<br/>';
          echo '<br>';
          echo $pr_w.'*'.$pr_h;
          echo '<br>';
          echo 'w '.self::$width/100*$pr_w;
          echo '<br>';
          echo 'h '.self::$height/100*$pr_w;
          echo '<br>';
          echo '<br>';
          echo 'w '.self::$width/100*$pr_h;
          echo '<br>';
          echo 'h '.self::$height/100*$pr_h;
          die();

          }
         */

        $new_image = imagecreatetruecolor($w, $h);

        $red = imagecolorallocate($new_image, 255, 255, 255);
        imagefill($new_image, 0, 0, $red);

        $new = self::calcNewRazmer(self::$width, self::$height, $w, $h);

        $str = '';
        if (isset($_GET['show_info'])) {
            foreach ($new['data'] as $k => $v) {
                $str .= $k . ':' . $v . PHP_EOL;
            }
        }
        imagecopyresampled(
                $new_image, self::$image, 0, 0, $new['data']['scr_x'], $new['data']['scr_y'], $w, $h, $new['data']['scr_w'], $new['data']['scr_h']
        );

        if (isset($_GET['show_info'])) {
            imagefilledrectangle($new_image, 10, 20, 350, 400, $red);
            $color = ImageColorAllocate($new_image, 0, 0, 0);
            ImageTTFtext($new_image, 14, 0, 20, 40, $color, $_SERVER['DOCUMENT_ROOT'] . DS . 'fonts' . DS . 'Roboto-Regular.ttf', $str);
        }

//echo '<br/>imagecopyresampled( '.$new_image.', '.self::$image.', 0, 0, '.$x1.', '.$y1.', '.$w.', '.$w.', '.$x2.', '.$y2.' );';
        self::$image = $new_image;
        self::getSize();
    }

    /**
     * Приватная функция, "открывающая" файл в зависимости от типа изображения.
     * @param $file string	Путь исходного файла
     */
    private static function openImage($file) {

        if (!file_exists($file))
            throw new \Exception('нет файла');

        self::$mime = mime_content_type($file);

        if (self::$mime == 'image/jpeg') {
            self::$image = imagecreatefromjpeg($file);
        } elseif (self::$mime == 'image/png') {
            self::$image = imagecreatefrompng($file);
        } elseif (self::$mime == 'image/gif') {
            self::$image = imagecreatefromgif($file);
        } else {
            throw new \Exception('тип файла не получилось определить');
        }
    }

}
