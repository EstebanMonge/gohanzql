<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Template Class
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 4.0.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

/* ----------------------------------------------------------------------------
 Class: Template rendering (Twig)
-------------------------------------------------------------------------------
 Replacement for the former PEAR class HTML_Template_IT. The templates are Twig
 files, but the pages still fill them with the well-known
 setVariable() / parse() / show() calls:

  - every top level "{% block name %}" of a template file is one template block
  - "{{ VARIABLE }}" is a placeholder of the block it is written in
  - "{{ __inner__ }}" is the slot in which the parsed output of the block "inner" is
    inserted into its parent block

 parse() renders one block with the variables set since the last parse() and appends
 the result to the block output; the variables are consumed by that. A block without
 any variable and without any parsed inner block is skipped. This is exactly the
 behaviour of HTML_Template_IT, so that the pages did not need to be changed.
 Name: NagTemplateClass
-----------------------------------------------------------------------------*/

namespace functions;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

class NagTemplateClass
{
    private const GLOBAL_BLOCK = '__global__';

    /* Define class variables */
    private $strRoot; /* Template root directory */
    private $strCacheDir; /* Directory for compiled templates, empty = no cache */
    private static $arrTwig = array(); /* Twig environments (one per template directory) */
    /** @var TemplateWrapper|null */
    private $objTemplate = null; /* Loaded template */
    private $arrBlockVars = array(); /* Block name => array of placeholder names */
    private $arrBlockInner = array(); /* Block name => array of inner block names */
    private $arrBlockData = array(); /* Block name => parsed output */
    private $arrVarCache = array(); /* Variables set, but not parsed yet */
    private $booGlobalParsed = false; /* Global block has been parsed */

    /**
     * NagTemplateClass constructor.
     * @param string $strRoot Template root directory
     * @param string $strCacheDir Directory for compiled templates (default: private directory in the temp dir)
     */
    public function __construct(string $strRoot, string $strCacheDir = '')
    {
        $this->strRoot = $strRoot;
        $this->strCacheDir = ($strCacheDir !== '') ? $strCacheDir : $this->getDefaultCacheDir();
    }

    /**
     * Load a template file
     * @param string $strFile Template file, relative to the root directory
     * @return bool true on success
     */
    public function loadTemplatefile(string $strFile): bool
    {
        $this->arrBlockVars = array();
        $this->arrBlockInner = array();
        $this->arrBlockData = array();
        $this->arrVarCache = array();
        $this->booGlobalParsed = false;
        $this->objTemplate = null;
        try {
            $objTwig = $this->getTwig();
            $strSource = $objTwig->getLoader()->getSourceContext($strFile)->getCode();
            $this->objTemplate = $objTwig->load($strFile);
        } catch (LoaderError $objError) {
            return false;
        }
        $this->readBlocks($strSource);
        return true;
    }

    /**
     * Set one or more placeholder values. Placeholders which do not exist in the template are ignored.
     * @param string|array $variable Placeholder name or array of name => value
     * @param mixed $value Value
     */
    public function setVariable($variable, $value = ''): void
    {
        if (is_array($variable)) {
            foreach ($variable as $strKey => $mixValue) {
                $this->setVariable($strKey, $mixValue);
            }
        } elseif ($this->placeholderExists(self::GLOBAL_BLOCK, $variable)) {
            $this->arrVarCache[$variable] = $value;
        }
    }

    /**
     * Render a block with the current variables and append the result to the block output
     * @param string $strBlock Block name
     * @return bool false if the block does not exist
     */
    public function parse(string $strBlock = self::GLOBAL_BLOCK): bool
    {
        if (!isset($this->arrBlockVars[$strBlock])) {
            return false;
        }
        if ($strBlock === self::GLOBAL_BLOCK) {
            $this->booGlobalParsed = true;
        }
        $this->parseBlock($strBlock);
        return true;
    }

    /**
     * Get the parsed output of a block
     * @param string $strBlock Block name
     * @return string
     */
    public function get(string $strBlock = self::GLOBAL_BLOCK): string
    {
        if ($strBlock === self::GLOBAL_BLOCK && !$this->booGlobalParsed) {
            $this->parse(self::GLOBAL_BLOCK);
        }
        return $this->arrBlockData[$strBlock] ?? '';
    }

    /**
     * Print the parsed output of a block
     * @param string $strBlock Block name
     */
    public function show(string $strBlock = self::GLOBAL_BLOCK): void
    {
        echo $this->get($strBlock);
    }

    /**
     * Render one block, including its inner blocks
     * @param string $strBlock Block name
     */
    private function parseBlock(string $strBlock): void
    {
        $arrContext = array();
        $booEmpty = true;
        foreach (array_keys($this->arrBlockVars[$strBlock]) as $strVar) {
            if (isset($this->arrVarCache[$strVar])) {
                $arrContext[$strVar] = $this->arrVarCache[$strVar];
                unset($this->arrVarCache[$strVar]);
                $booEmpty = false;
            }
        }
        foreach ($this->arrBlockInner[$strBlock] ?? array() as $strInner) {
            $this->parseBlock($strInner);
            if ($this->arrBlockData[$strInner] !== '') {
                $booEmpty = false;
            }
            $arrContext['__' . $strInner . '__'] = $this->arrBlockData[$strInner];
            $this->arrBlockData[$strInner] = '';
        }
        if (!$booEmpty) {
            $this->arrBlockData[$strBlock] .= $this->objTemplate->renderBlock($strBlock, $arrContext);
        }
    }

    /**
     * Check if a placeholder exists in a block or in one of its inner blocks
     * @param string $strBlock Block name
     * @param string $strVar Placeholder name
     * @return bool
     */
    private function placeholderExists(string $strBlock, string $strVar): bool
    {
        if (isset($this->arrBlockVars[$strBlock][$strVar])) {
            return true;
        }
        foreach ($this->arrBlockInner[$strBlock] ?? array() as $strInner) {
            if ($this->placeholderExists($strInner, $strVar)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Read the block structure of a template: the placeholders and the inner blocks of every block
     * @param string $strSource Template source
     */
    private function readBlocks(string $strSource): void
    {
        preg_match_all(
            '/\{%\s*block\s+(\w+)\s*%\}(.*?)\{%\s*endblock(?:\s+\w+)?\s*%\}/s',
            $strSource,
            $arrBlocks,
            PREG_SET_ORDER
        );
        foreach ($arrBlocks as $arrBlock) {
            $this->arrBlockVars[$arrBlock[1]] = array();
            $this->arrBlockData[$arrBlock[1]] = '';
        }
        foreach ($arrBlocks as $arrBlock) {
            preg_match_all('/\{\{\s*([A-Za-z_]\w*)\s*\}\}/', $arrBlock[2], $arrVars);
            foreach ($arrVars[1] as $strVar) {
                $this->arrBlockVars[$arrBlock[1]][$strVar] = true;
                if (preg_match('/^__(\w+)__$/', $strVar, $arrInner) && isset($this->arrBlockVars[$arrInner[1]])
                    && !in_array($arrInner[1], $this->arrBlockInner[$arrBlock[1]] ?? array(), true)) {
                    $this->arrBlockInner[$arrBlock[1]][] = $arrInner[1];
                }
            }
        }
    }

    /**
     * Get a directory for the compiled templates. The compiled templates are PHP code, so the directory must not be
     * usable by other users: it is only used if it belongs to the current user and is not accessible for others.
     * @return string Directory or empty string if there is no safe directory
     */
    private function getDefaultCacheDir(): string
    {
        if (!function_exists('posix_geteuid')) {
            return '';
        }
        $intUser = posix_geteuid();
        $strDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nagiosql_twig_' . $intUser;
        if (!is_dir($strDir) && !@mkdir($strDir, 0700) && !is_dir($strDir)) {
            return '';
        }
        if (is_link($strDir) || fileowner($strDir) !== $intUser || (fileperms($strDir) & 077) !== 0) {
            return '';
        }
        return is_writable($strDir) ? $strDir : '';
    }

    /**
     * Get the Twig environment
     * @return Environment
     */
    private function getTwig(): Environment
    {
        $strKey = $this->strRoot . "\0" . $this->strCacheDir;
        if (!isset(self::$arrTwig[$strKey])) {
            self::$arrTwig[$strKey] = new Environment(new FilesystemLoader($this->strRoot), array(
                'autoescape' => false, /* The pages hand over HTML, escaping is done there */
                'strict_variables' => false, /* Unset placeholders are empty */
                'cache' => $this->strCacheDir !== '' ? $this->strCacheDir : false,
                'auto_reload' => true
            ));
        }
        return self::$arrTwig[$strKey];
    }
}
