# AutoValue PHP

## <a name="howto"></a>How to use AutoValue

The AutoValue concept is extremely simple: **You write an abstract class, and
AutoValue implements it.**

**Note:** Below, we will illustrate an AutoValue class *without* a generated
builder class. If you're more interested in the builder support, continue
reading at [AutoValue with Builders](builders.md) instead.

### <a name="example_php"></a>In your value class

Create your value class as an *abstract* class, with an abstract accessor method
for each desired property, an equals method, and bearing the `@AutoValue` annotation.

```php
/**
 * @AutoValue
 */
abstract class Animal
{
  static function create(String $name, int $numberOfLegs): self
  {
    return new AutoValue_Animal([
      'name' => $name,
      'numberOfLegs' => $numberOfLegs,
    ]);
  }

  abstract function name(): string;
  abstract function numberOfLegs(): int;
  abstract function equals($value): bool;
}
```

Note that in real life, some classes and methods would presumably have PHPDoc.
We're leaving these off in the User Guide only to keep the examples clean and
short.

### <a name="installation"></a>Installation

Install AutoValue in your project using [Composer](https://getcomposer.org).

```bash
composer require space48/auto-value --dev
```

Note that AutoValue should be installed as a dev dependency as it does not need
to be loaded in your project at runtime.

### <a name="usage"></a>Usage

Your choice to use AutoValue is essentially *API-invisible*. That means that to
the consumer of your class, your class looks and functions like any other. The
simple test below illustrates that behavior. Note that in real life, you would
write tests that actually *do something interesting* with the object, instead of
only checking field values going in and out.

```php
public function testAnimal() {
  $dog = Animal::create('dog', 4);
  self::assertEquals('dog', $dog->name());
  self::assertEquals(4, $dog->numberOfLegs());

  // You probably don't need to write assertions like these; just illustrating.
  self::assertTrue(Animal::create('dog', 4)->equals($dog));
  self::assertFalse(Animal::create('cat', 4)->equals($dog));
  self::assertFalse(Animal::create('dog', 2)->equals($dog));
  self::assertFalse(Animal::create('dog', 2)->equals('banana'));
}
```

### <a name="build"></a>Building the AutoValue classes

When you create a new class bearing the `@AutoValue` annotation, or modify or
remove such a class, you must run AutoValue's build command in order to rebuild
the AutoValue classes.

```bash
vendor/bin/auto build path/to/project/src
```

### <a name="whats_going_on"></a>What's going on here?

AutoValue searches the specified source directory for any abstract PHP classes
bearing the `@AutoValue` annotation. It reads your abstract class and infers
what the implementation class should look like. It generates source code, in
your package, of a concrete implementation class which extends your abstract
class, having:

*   one property for each of your abstract accessor methods
*   a constructor that sets these fields
*   a concrete implementation of each accessor method returning the associated
    property value
*   an `equals` implementation that compares these values in the usual way

Your hand-written code, as shown above, delegates its factory method call to the
generated constructor and voilà!

For the `Animal` example shown above, here is [typical code AutoValue might
generate](generated-example.md).

Note that *consumers* of your value class *don't need to know any of this*. They
just invoke your provided factory method and get a well-behaved instance back.

## <a name="why"></a>Why should I use AutoValue?

See [Why AutoValue?](why.md).

## <a name="more_howto"></a>How do I...

How do I...

*   ... [also generate a **builder** for my value class?](howto.md#builder)
*   ... [include `with-` methods on my value class for creating slightly
    **altered** instances?](howto.md#withers)
*   ... [use (or not use) JavaBeans-style name **prefixes**?](howto.md#beans)
*   ... [use **nullable** properties?](howto.md#nullable)
*   ... [perform other **validation**?](howto.md#validate)
*   ... [use a property of a **mutable** type?](howto.md#mutable_property)
*   ... [use a **custom** implementation of `equals`, etc.?](howto.md#custom)
*   ... [have multiple **`create`** methods, or name it/them
    differently?](howto.md#create)
*   ... [**ignore** certain properties in `equals`, etc.?](howto.md#ignore)
*   ... [have AutoValue also implement abstract methods from my
    **supertypes**?](howto.md#supertypes)
*   ... [also include **setter** (mutator) methods?](howto.md#setters)
*   ... [have one `@AutoValue` class **extend** another?](howto.md#inherit)
*   ... [keep my accessor methods **private**?](howto.md#private_accessors)
*   ... [expose a **constructor**, not factory method, as my public creation
    API?](howto.md#public_constructor)
*   ... [use AutoValue on an **interface**, not abstract class?](howto.md#interface)
*   ... [**memoize** ("cache") derived properties?](howto.md#memoize)
