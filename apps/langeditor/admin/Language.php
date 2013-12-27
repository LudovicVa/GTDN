<?php
/**
 * Language represents the data associated with a language
 *
 * @package apps\langeditor\admin
 * @author Ludovic Vanhove
 * @version 0.1.0-29-10-2013
 */

class Language {

    /**
     * @var array Language values associated to their constant
     */
    private $values = array();

    /**
     * @var string Language ID
     */
    private $identifier = "";

    /**
     * Constructor of language associated with a file
     *
     * @param string $identifier language identifier
     * @param string $file file path to the file
     */
    function __construct($identifier, $file) {
        loadLangFile($file);
        $this->identifier = $identifier;
    }

    /**
     * Constructor of empty language
     *
     * @param string $identifier language identifier
     */
    function __construct($identifier) {
        $this->identifier = $identifier;
    }


    /**
     * Assigns a new language constant.
     * If the constant was already defined, it will keep the previous value by default.
     *
     * @param string $name  name as it is in the lang file
     * @param string $value value as it is after compiling the lang file
     * @param bool $overwrite Forces the reassignement of a new value.
     */
    public function assign($name, $value, $overwrite = false) {
        if ((!isset($this->values[$name]) || $overwrite) && !empty($name) && !empty($value)) {
            $this->values[$name] = $value;
        }
    }


    /**
     * Loads a language file.
     *
     * @param string $dir language file path without its extension and without the locale identifier
     */
    private function loadLangFile($file) {
        // Checks that file exists and not already loaded
        if (file_exists($file)) {
            // Parses XML file
            $string = file_get_contents($file);
            $xml = new SimpleXMLElement($string);
            foreach ($xml->item as $lang_item) {
                $lang_string = dom_import_simplexml($lang_item)->nodeValue;
                $this->assign((string) $lang_item->attributes()->id, $lang_string);
            }
        }
    }

    /**
     * Save into a language file.
     *
     * @param string $dir language file path without its extension and without the locale identifier
     */
    public function saveLangFile($file) {
        //constructs the object
        $lang = new SimpleXMLElement("<lang value=\"fr\"></lang>");

        foreach ($this->values as $key=>$value) {
            $val = $lang->addChild('item', $value);
            $val->addAttribute('key', $key);
        }

        echo $lang->asXML();
    }

    /**
     * Returns the value in the current language associated to the $name key.
     *
     * @param  string $name name as it is in the lang file
     * @return string value as it is after compiling the lang file
     */
    public function get($name, $params = null) {
        if (!empty($name)) {
            if (!isset($this->values[$name])) {
                foreach ($this->lang_dirs as $dir_name => $dir) {
                    foreach ($this->languages as $lang) {
                        if (isset($dir[$lang])) {
                            $this->loadLangFile($dir[$lang]);

                            // Remove the directory treated
                            unset($this->lang_dirs[$dir_name][$lang]);
                        }
                    }

                    // Load default file
                    if (!isset($this->values[$name]) && isset($dir['default']) && isset($this->lang_dirs[$dir_name][$dir['default']])) {
                        $this->loadLangFile($dir[$dir['default']]);
                    }
                }
            }

            if (isset($this->values[$name])) {
                if (!empty($params)) {
                    if (!is_array($params)) {
                        $params = array($this->values[$name], $params);
                    } else {
                        array_unshift($params, $this->values[$name]);
                    }
                    return call_user_func_array('sprintf', $params);
                } else {
                    return $this->values[$name];
                }
            }
        }

        return $name;
    }
} 