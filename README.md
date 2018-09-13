# former
Form validate with annotation

## Installation
Package is available on [Packagist](http://packagist.org/packages/layliaiyong/former),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require layliaiyong/former
```

[PHP](https://php.net) 5.4+

## Annotation
Property must be protected. Annotation option value must be json string.

Annotation Patterns: 
+ Former...

End with [https://github.com/Respect/Validation](https://github.com/Respect/Validation "respect/Validation") validator
```PHP
class TestFormer extends Former
{
    /**
     * @FormerNotEmpty {"message":"ID must not be empty"}
     * @FormerIntVal {"message":"ID must be integer"}
     */
    protected $id;
}

$data = new \stdClass();
$validator = new TestFormer();
// pass
$data->id = 1;
$valid = $validator->input($data)->validate();// true
$errors = $validator->errors();// []
// error
$data->id = 'abc'; 
$valid = $validator->input($data)->validate();// false
$errors = $validator->errors();// ["ID must be integer"]
```

+ NotFormer... 

End with `respect/Validation` validator
```PHP
class TestFormer extends Former
{
    /**
     * @NotFormerIntVal {"message":"ID must not be integer"}
     */
    protected $id;
}

$data = new \stdClass();
$validator = new TestFormer();
// pass
$data->id = 1;
$valid = $validator->input($data)->validate();// false
$errors = $validator->errors();// ["ID must be integer"]
// error
$data->id = 'abc'; 
$valid = $validator->input($data)->validate();// true
$errors = $validator->errors();// []
```

+ Former

```PHP
class TestFormer extends Former
{
    /**
     * @FormerObjectType {"message":"former must be object"}
     * @Former {"message":"invalid former","former":"\\TestObjectFormer"}
     */
    protected $former;
}
class TestObjectFormer extends Former
{
    /**
     * @FormerNotEmpty {"message":"ID must not be empty"}
     * @FormerIntVal {"message":"ID must be integer"}
     */
    protected $id;
}

$data = new \stdClass();
$former = new \stdClass();
$validator = new TestFormer();
// pass
$former->id = 1;
$data->former = $former;
$valid = $validator->input($data)->validate();// true
$errors = $validator->errors();// []
// error
$former->id = 'abc'; 
$data->former = $former;
$valid = $validator->input($data)->validate();// false
$errors = $validator->errors();// ["[former]invalid former","[id]ID must be integer"]
```
+ Formers

```PHP
class TestFormer extends Former
{
    /**
     * @FormerObjectType {"message":"formers must be object array"}
     * @Former {"message":"invalid formers","former":"\\TestObjectFormer"}
     */
    protected $formers;
}
class TestObjectFormer extends Former
{
    /**
     * @FormerNotEmpty {"message":"ID must not be empty"}
     * @FormerIntVal {"message":"ID must be integer"}
     */
    protected $id;
}

$data = new \stdClass();
$former = new \stdClass();
$validator = new TestFormer();
// pass
$former->id = 1;
$data->formers = [$former];
$valid = $validator->input($data)->validate();// true
$errors = $validator->errors();// []
// error
$former->id = 'abc'; 
$data->formers = [$former];
$valid = $validator->input($data)->validate();// false
$errors = $validator->errors();// ["[formers]invalid formers","[id]ID must be integer"]
```
+ CaseFormer

```PHP
class TestFormer extends Former
{
    /**
     * @CaseFormer [{"message":"invalid case when value is 1","when":"FormerEquals","params":[1],"former":"\\TestCaseFormer"}]
     */
    protected $case;
    protected $case1;
}
class TestCaseFormer extends Former
{
    /**
     * @FormerNotEmpty {"message":"ID must not be empty"}
     * @FormerIntVal {"message":"ID must be integer"}
     */
    protected $case1;
}

$data = new \stdClass();
$validator = new TestFormer();
// pass
$data->case = 1;
$data->case1 = 2;
$valid = $validator->input($data)->validate();// true
$errors = $validator->errors();// []
// error
$data->case = 1;
$data->case1 = 'abc';
$valid = $validator->input($data)->validate();// false
$errors = $validator->errors();// ["[case]invalid case when value is 1","[case1]ID must be integer"]
```
