# AutoValue with Builders

The [introduction](index.md) of this User Guide covers the basic usage of
AutoValue using a static factory method as your public creation API. But in many
circumstances (such as those laid out in *Effective Java, 2nd Edition* Item 2),
you may prefer to let your callers use a *builder* instead.

Fortunately, AutoValue can generate builder classes too! This page explains how.
Note that we recommend reading and understanding the basic usage shown in the
[introduction](index.md) first.

## How to use AutoValue with Builders<a name="howto"></a>

As explained in the introduction, the AutoValue concept is that **you write an
abstract value class, and AutoValue implements it**. Builder generation works in
the exact same way: you also create an abstract builder class, nesting it inside
your abstract value class, and AutoValue generates implementations for both.

### In `Animal.php`<a name="example_php_value"></a>

```php
/**
 * @AutoValue
 */
abstract class Animal
{
  abstract function name(): string;
  abstract function numberOfLegs(): int;

  static function builder(): AnimalBuilder
  {
    return new AutoValue_AnimalBuilder();
  }
}
```

### In `AnimalBuilder.php`<a name="example_php_builder"></a>

```php
/**
 * @AutoValue\Builder
 */
abstract class AnimalBuilder
{
  abstract function name(string $value): self;
  abstract function numberOfLegs(int $value): self;
  abstract function build(): Animal;
}
```

Note that in real life, some classes and methods would presumably have PHPDoc.
We're leaving these off in the User Guide only to keep the examples clean and
short.

### Usage<a name="usage"></a>

```php
public function testAnimal()
{
  $dog = Animal::builder()->name("dog")->numberOfLegs(4)->build();
  self::assertEquals("dog", $dog->name());
  self::assertEquals(4, $dog->numberOfLegs());

  // You probably don't need to write assertions like these; just illustrating.
  self::assertTrue(
      Animal::builder()->name("dog")->numberOfLegs(4)->build()->equals($dog));
  self::assertFalse(
      Animal::builder()->name("dog")->numberOfLegs(4)->build()->equals($dog));
  self::assertFalse(
      Animal::builder()->name("dog")->numberOfLegs(2)->build()->equals($dog));
}
```

### What does AutoValue generate?<a name="generated"></a>

For the `Animal` example shown above, here is [typical code AutoValue might
generate](generated-builder-example.md).

## <a name="howto"></a>How do I...

*   ... [use (or not use) `set` **prefixes**?](builders-howto.md#beans)
*   ... [use different **names** besides
    `builder()`/`Builder`/`build()`?](builders-howto.md#build_names)
*   ... [specify a **default** value for a property?](builders-howto.md#default)
*   ... [initialize a builder to the same property values as an **existing**
    value instance](builders-howto.md#to_builder)
*   ... [**validate** property values?](builders-howto.md#validate)
