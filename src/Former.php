<?php

namespace Lay\Former;

use Respect\Validation\Validator;
use DocBlockReader\Reader;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

defined('LAY_FORMER_ERROR_MESSAGE') or define('LAY_FORMER_ERROR_MESSAGE', 'invalid form parameter');
defined('LAY_FORMER_ERROR_FIRST_SUSPEND') or define('LAY_FORMER_ERROR_FIRST_SUSPEND', false);

/**
 */
abstract class Former
{
    private $_errors = [];
    public function __construct($form = [])
    {
        if (!empty($form) && (is_object($form) || is_array($form))) {
            foreach ($form as $key => $value) {
                if($key == '_errors') {
                    continue;
                }
                $this->$key = $value;
            }
        }
    }

    public function __set($prop, $value)
    {
        $this->$prop = $value;
    }

    public function __get($prop)
    {
        if (isset($this->$prop)) {
            return $this->$prop;
        }

        return null;
    }

    public function __isset($prop)
    {
        return array_key_exists($prop, get_object_vars($this));
        // return isset($this->$prop);
    }

    /**
     * 初始化表单
     */
    public function input($form = [])
    {
        if (!empty($form) && (is_object($form) || is_array($form))) {
            foreach ($form as $key => $value) {
                if($key == '_errors') {
                    continue;
                }
                $this->$key = $value;
            }
        }
        return $this;
    }
    /**
     * 错误信息
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * 验证表间，只验证受保护的属性
     */
    public function validate()
    {
        $reflectionClass = new ReflectionClass($this);
        $props = $reflectionClass->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($props as $prop) {
            $prop->setAccessible(true);// 设置为可访问
            $name = $prop->getName();
            $value = $prop->getValue($this);
            $reader = new Reader($this, $name, 'property');
            $params = $reader->getParameters();
            // $comments = $prop->getDocComment();
        // echo "\n";var_dump([$params, $comments]);
            if(!empty($params)) {
                foreach ($params as $param => $option) {
                    // 匹配规则 
                    switch (strtolower($param)) {
                        case 'former':
                            $valid = $this->validateFormer($name, $value, $param, $option);
                            break;
                        case 'formers':
                            $valid = $this->validateFormers($name, $value, $param, $option);
                            break;
                        case 'caseformer':
                            $valid = $this->validateCaseFormer($name, $value, $param, $option);
                            break;

                        default:
                            $valid = $this->validateBase($name, $value, $param, $option);
                            break;
                    }
                    if(!$valid && LAY_FORMER_ERROR_FIRST_SUSPEND) {
                        // 跳过更多验证
                        break 2;
                    }
                }
            }
        }
        return empty($this->_errors) ? true : false;
    }
    /**
     * 基础验证
     */
    private function validateBase($name, $value, $param, $option)
    {
        $json = $this->convertOption2JsonObject($option);
        $not = false;
        // Vaidation规则
        if(strpos($param, 'Former') === 0) {
            $rule = substr($param, 6);
        } else if(strpos($param, 'NotFormer') === 0) {
            $rule = substr($param, 9);
            $not = true;
        }
        // 使用第三方库进行验证
        if(!empty($json->each) && is_string($json->each)) {
            // 子验证
            $validator = call_user_func_array([Validator::class, $rule], []);
            if(strpos($json->each, 'Former') === 0) {
                $eachRule = substr($json->each, 6);
                $eachValidator = call_user_func_array([Validator::class, $eachRule], $json->params);
            } else if(strpos($json->each, 'NotFormer') === 0) {
                // 反向
                $eachRule = substr($json->each, 9);
                $eachValidator = call_user_func_array([Validator::class, $eachRule], $json->params);
                $eachValidator = Validator::not($eachValidator);
            } else {
                throw new FormerException('Invalid former option value on property '.get_class($this).'::'.$name.', "each" value must start with "Former" or "NotFormer"');
            }
            $valid = $not ? Validator::not($validator->each($eachValidator))->validate($value) : $validator->each($eachValidator)->validate($value);
        } else {
            $validator = call_user_func_array([Validator::class, $rule], $json->params);
            $valid = $not ? Validator::not($validator)->validate($value) : $validator->validate($value);
        }
        // 验证不正确
        if(empty($valid)) {
            $this->_errors[] = sprintf('[%s]' . $json->message, $name);
            if(LAY_FORMER_ERROR_FIRST_SUSPEND) {
                // 跳过更多验证
                return $valid;
            }
        }
        return $valid;
    }
    /**
     * 子Former表单验证
     */
    private function validateFormer($name, $value, $param, $option)
    {
        $json = $this->convertOption2JsonObject($option);
        // 子Former表单
        if(!empty($json->former) && $this->isFormerClass($json->former)) {
            $clazz = $json->former;
        } else {
            throw new FormerException('Invalid former option value on property '.get_class($this).'::'.$name.',"former" value must be class extends '.Former::class);
        }
        $former = new $clazz();
        $valid = $former->input($value)->validate();
        if(!$valid) {
            $this->_errors[] = sprintf('[%s]' . $json->message, $name);
            $this->_errors = array_merge($this->_errors, $former->_errors);
            if(LAY_FORMER_ERROR_FIRST_SUSPEND) {
                // 跳过更多验证
                return $valid;
            }
        }
        return $valid;
    }
    /**
     * 子Former表单数组验证
     */
    private function validateFormers($name, $value, $param, $option)
    {
        $json = $this->convertOption2JsonObject($option);
        // 子Former表单数组
        if(!empty($json->former) && $this->isFormerClass($json->former)) {
            $clazz = $json->former;
        } else {
            throw new FormerException('Invalid former option value on property '.get_class($this).'::'.$name.',"former" value must be class extends '.Former::class);
        }
        $valid = true;
        if(is_array($value)) {
            foreach ($value as $val) {
                $former = new $clazz();
                $valid = $former->input($val)->validate();
                if(!$valid) {
                    $this->_errors[] = sprintf('[%s]' . $json->message, $name);
                    $this->_errors = array_merge($this->_errors, $former->_errors);
                    if(LAY_FORMER_ERROR_FIRST_SUSPEND) {
                        // 跳过更多验证
                        return $valid;
                    }
                }
            }
        }
        return $valid;
    }
    /**
     * 条件Former验证
     */
    private function validateCaseFormer($name, $value, $param, $option)
    {
        $jsons = $this->convertOption2JsonObjectArray($option);
        $valid = true;
        foreach ($jsons as $json) {
            if(stripos($json->when, 'Former') === 0) {
                    $whenRule = substr($json->when, 6);
                    $whenValidator = call_user_func_array([Validator::class, $whenRule], $json->params);
            } else if(stripos($json->when, 'NotFormer') === 0) {
                // 反向
                $whenRule = substr($json->when, 9);
                $whenValidator = call_user_func_array([Validator::class, $whenRule], $json->params);
                $whenValidator = Validator::not($whenValidator);
            } else {
                throw new FormerException('Invalid former option value on property '.get_class($this).'::'.$name.', "when" value must start with "Former" or "NotFormer"');
            }
            $when = $whenValidator->validate($value);
            if($when) {
                // 条件Former表单
                if(!empty($json->former) && $this->isFormerClass($json->former)) {
                    $clazz = $json->former;
                } else {
                    throw new FormerException('Invalid former option value on property '.get_class($this).'::'.$name.',"former" value must be class extends '.Former::class);
                }
                $former = new $clazz();
                $valid = $former->input($this)->validate();
                if(!$valid) {
                    $this->_errors[] = sprintf('[%s]' . $json->message, $name);
                    $this->_errors = array_merge($this->_errors, $former->_errors);
                    if(LAY_FORMER_ERROR_FIRST_SUSPEND) {
                        // 跳过更多验证
                        return $valid;
                    }
                }
            }
        }
        return $valid;
    }
    // 是否Former类名称
    private function isFormerClass($clazz)
    {
        if(is_string($clazz) && class_exists($clazz) && is_subclass_of($clazz, Former::class)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Former选项转JSON对象
     */
    private function convertOption2JsonObject($option)
    {
        // 设置验证参数、失败信息
        if(!is_array($option)) {
            $option = [
                'params' => [],
                'message' => LAY_FORMER_ERROR_MESSAGE 
            ];
        }
        if(empty($option['message'])) {
            // 设置验证失败默认提示信息
            $option['message'] = LAY_FORMER_ERROR_MESSAGE;
        }
        if(empty($option['params']) || !is_array($option['params'])) {
            $option['params'] = [];
        } else {
            $option['params'] = array_values($option['params']);
        }
        $json = json_decode(json_encode($option));

        return $json;
    }
    /**
     * Former选项转JSON对象数组
     */
    private function convertOption2JsonObjectArray($option)
    {
        if(!is_array($option)) {
            $option = [];
            $option[] = [
                'params' => [],
                'message' => LAY_FORMER_ERROR_MESSAGE 
            ];
            $json = json_decode(json_encode($option));
        } else {
            $json = [];
            $tmp = json_decode(json_encode($option));
            if(is_object($tmp)) {
                if(empty($tmp->message)) {
                    // 设置验证失败默认提示信息
                    $tmp->message = LAY_FORMER_ERROR_MESSAGE;
                }
                if(empty($tmp->params) || !is_array($tmp->params)) {
                    $tmp->params = [];
                } else {
                    $tmp->params = array_values($tmp->params);
                }
                $json[] = $tmp;
            } else {
                foreach ($tmp as $val) {
                    if(!is_object($val)) {
                        $item = [
                            'params' => [],
                            'message' => LAY_FORMER_ERROR_MESSAGE 
                        ];
                        $json[] = json_decode(json_encode($item));
                    } else {
                        if(empty($val->message)) {
                            // 设置验证失败默认提示信息
                            $val->message = LAY_FORMER_ERROR_MESSAGE;
                        }
                        if(empty($val->params) || !is_array($val->params)) {
                            $val->params = [];
                        } else {
                            $val->params = array_values($val->params);
                        }
                        $json[] = $val;
                    }
                }
            }
        }
        return $json;
    }
}
