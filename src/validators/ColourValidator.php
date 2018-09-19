<?php
namespace fruitstudios\colorit\validators;

use Craft;
use yii\validators\Validator;

class ColourValidator extends Validator
{
    public $format = 'hex';

    // Public Methods
    // =========================================================================

    public function validateValue($value)
    {
        switch ($this->format)
        {
            case 'hex':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            case 'rgb':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            case 'rgba':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            default:
                return false;
                break;
        }
    }

}
