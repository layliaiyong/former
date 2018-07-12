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
                $this->$key = $value;
            }
        }
        return $this;
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
            if(!empty($params)) {
                foreach ($params as $param => $option) {
                    $not = false;
                    if(strpos($param, 'Former') === 0) {
                        $rule = substr($param, 6);
                    } else if(strpos($param, 'NotFormer') === 0) {
                        $rule = substr($param, 9);
                        $not = true;
                    }
                    if(!empty($rule)) {
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
                        // 验证
                        $rule = substr($param, 6);
                        $json = json_decode(json_encode($option));
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
                                throw new FormerPatternException('Invalid former pattern, must start with "Former" or "NotFormer"');
                            }
                            $ret = $not ? Validator::not($validator->each($eachValidator))->validate($value) : $validator->each($eachValidator)->validate($value);
                        } else {
                            $validator = call_user_func_array([Validator::class, $rule], $json->params);
                            $ret = $not ? Validator::not($validator)->validate($value) : $validator->validate($value);
                        }
                        // 验证不正确
                        if(empty($ret)) {
                            $this->_errors[] = sprintf($json->message.'[%s]', $name);
                            if(LAY_FORMER_ERROR_FIRST_SUSPEND) {
                                // 路过更多验证
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        return empty($this->_errors) ? true : false;
    }
}
