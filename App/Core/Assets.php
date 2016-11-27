<?php
namespace PdfFormsLoader\Core;


class Assets
{
    private $publicUrl;

    public function __construct()
    {
        $this->publicUrl = plugins_url( 'public/', dirname(__FILE__) );
    }

    /**
     * @return mixed
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    public static function __callStatic( $method , $arguments ) {
        $method = str_ireplace('Static', '', $method);
        return call_user_func_array(array((new self), $method), $arguments);
    }

    public function getImageUrl( $fileName, $path = '' )
    {
        return $this->getResourceUrl( $fileName, 'images', $path );
    }

    public function getCssUrl( $fileName, $path = '' )
    {
        return $this->getResourceUrl( $fileName, 'css', $path );
    }

    public function getJsUrl( $fileName, $path = '' )
    {
        return $this->getResourceUrl( $fileName, 'js', $path );
    }

    public function getResourceUrl( $fileName, $type, $path = '' )
    {
        return $this->getPublicUrl() . $path .  '/' . $type . '/' . $fileName;
    }
}