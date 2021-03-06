<?php
namespace DRKTettnang\Homepage\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;

class LinkViewHelper extends AbstractViewHelper
{


    /**
     * NOTE: This property has been introduced via code migration to ensure backwards-compatibility.
     * @see AbstractViewHelper::isOutputEscapingEnabled()
     * @var boolean
     */
    protected $escapeOutput = false;

    const EMAILPATTERN = '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/';

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs strip_tags() function.
     *
     * @param string $value string to format
     * @return mixed
     * @see http://www.php.net/manual/function.strip-tags.php
     * @api
     */
    public function render($value = null)
    {
        return self::renderStatic(array('value' => $value), $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * Applies strip_tags() on the specified value.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = key_exists('value', $arguments) ? $arguments['value'] : null;
        if ($value === null) {
            $value = $renderChildrenClosure();
        }
        if (is_string($value) || (is_object($value) && method_exists($value, '__toString'))) {
            $html = preg_replace_callback('/\[([^]]+)\]\(([^)]+)\)/i', array('self', 'encode_link'), $value);
            $html = preg_replace_callback('#<a ([^>]*)href=(?:\'([^\']*)\'|"([^"]*)")([^>]*)>([^<]*)</ ?a>#i', array('self', 'check_link'), $html);
            $html = preg_replace_callback(self::EMAILPATTERN, array('self', 'encode_email'), $html);

            return $html;
        }

        return $value;
    }

    public static function check_link($matches)
    {
        $before = $matches[1];
        $href = $matches[3];
        $after = $matches[4];
        $inner = $matches[5];

        $return = '<a '.$before;

        if (preg_match('/^mailto[:].+/i', $href)) {
            $return .= preg_replace_callback('/mailto:(.+)@(.+)/i', array('self', 'obfuscate_mailto'), $href);
        } else {
            $return .= "href='$href'";
        }

        $return .= $after.'>';

        $inner = preg_replace_callback(self::EMAILPATTERN, array('self', 'obfuscate_email'), $inner);

        $return .= $inner.'</a>';

        return $return;
    }

    public static function obfuscate_email($matches)
    {
        $mail = $matches[0];

        return str_replace('@', '<span class="ta"></span>', $mail);
    }

    public static function obfuscate_mailto($matches)
    {
        $node = $matches[1];
        $domain = $matches[2];

        return "data-edon='$node' data-niamod='$domain'";
    }

    public static function encode_email($matches)
    {
        $mail = $matches[0];

        $attr = preg_replace_callback('/(.+)@(.+)/i', array('self', 'obfuscate_mailto'), $mail);
        $mail = self::obfuscate_email(array($mail));

        return "<a $attr>$mail</a>";
    }

    public static function encode_link($matches)
    {
        $inner = $matches[1];
        $href = $matches[2];

        if (preg_match(self::EMAILPATTERN, $href)) {
            $href = 'mailto:'.$href;
        }

        return '<a href="'.$href.'">'.$inner.'</a>';
    }
}
