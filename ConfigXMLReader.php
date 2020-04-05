<?php

    class ConfigXMLReader extends AbstractAdvertisementXMLReader{

        /*
            Парсит наценки
        */
        protected function parseRatio() {

            if($this->reader->nodeType == XMLREADER::ELEMENT && $this->reader->localName == 'Ratio') {
                
                $ratio = array(
                    'group_id' => $this->reader->getAttribute('group_id'),
                    'id'       => $this->reader->getAttribute('id'),
                    'value'    => $this->reader->getAttribute('value')
                );
                
                $this->reader->read();
                if($this->reader->nodeType == XMLREADER::TEXT)
                    $ratio['name'] = $this->reader->value;
                
                $this->result['ratios'][] = $ratio;
            }
        }

        /*
            Парсит настройки несовместимых наценок
        */
        protected function parseRatioException() {

            if($this->reader->nodeType == XMLREADER::ELEMENT && $this->reader->localName == 'RatioException') {
                $ratioException = array(
                    'id_1' => $this->reader->getAttribute('id_1'),
                    'id_2' => $this->reader->getAttribute('id_2')
                );

                $this->result['ratioExceptions'][] = $ratioException;
            }
        }

    }