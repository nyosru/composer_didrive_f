<?php
    
    /*
        Родительский класс для XML импортеров.
    */
    class AbstractAdvertisementXMLReader {
        
        protected $reader;
        protected $result = array();
        
        // события
        protected $_eventStack = array();

        /*
            Конструктор класса.

            Создает сущность XMLReader и загружает xml, либо бросает исключение
        */
        public function __construct($xml_path) {
            
            $this->reader = new XMLReader();

            if(is_file($xml_path))
                $this->reader->open($xml_path);
            else throw new Exception('XML file {'.$xml_path.'} not exists!');
        }

        /*
            Потоково парсит xml и вызывает методы для определенных элементов

            напр.
                при обнаружении элемента <Rubric> попытается вызвать метод parseRubric
            
            все методы парсинга должны быть public или protected.
        */
        public function parse() {
            
            $this->reader->read();
            
            while($this->reader->read()) {
                if($this->reader->nodeType == XMLREADER::ELEMENT) {

                    $fnName = 'parse' . $this->reader->localName;

                    if(method_exists($this, $fnName)) {

                        $lcn = $this->reader->name;

                        // стреляем по началу парсинга блока
                        $this->fireEvent('beforeParseContainer', array('name' => $lcn));

                        // пробежка по детям
                        if($this->reader->name == $lcn && $this->reader->nodeType != XMLREADER::END_ELEMENT) {
                            // стреляем событие до парсинга элемента
                            $this->fireEvent('beforeParseElement', array('name' => $lcn));
                            // вызываем функцию парсинга
                            $this->{$fnName}();
                            // стреляем событием по названию элемента
                            $this->fireEvent($fnName);
                            // стреляем событием по окончанию парсинга элемента
                            $this->fireEvent('afterParseElement', array('name' => $lcn));

                        }
                        elseif($this->reader->nodeType == XMLREADER::END_ELEMENT) {
                            // стреляем по окончанию парсинга блока
                            $this->fireEvent('afterParseContainer', array('name' => $lcn));
                        }
                    }
                }
            }
        }

        /*
            Вызывается при каждом распознавании
        */
        public function onEvent($event, $callback) {

            if(!isset($this->_eventStack[$event])) //!is_array($this->_eventStack[$event]))
                $this->_eventStack[$event] = array();

            $this->_eventStack[$event][] = $callback;

            return $this;
        }

        /*
            Выстреливает событие
        */
        public function fireEvent($event, $params = null, $once = false) {

            if($params == null) $params = array();

            $params['context'] = $this;

            if(!isset($this->_eventStack[$event]))
                return false;

            $count = count($this->_eventStack[$event]);
            
            if(isset($this->_eventStack[$event]) && $count > 0) {
                for($i = 0; $i < $count; $i++) {
                    call_user_func_array($this->_eventStack[$event][$i], $params);
                    
                    if($once == true) {
                        array_splice($this->_eventStack[$event], $i, 1);
                    }
                }
            }
        }

        /*
            Получить результаты парсинга
        */
        public function getResult() {
            return $this->result;
        }
        
        /*
            Очистить результаты парсинга
        */
        public function clearResult() {
            $this->result = array();
        }

    }
