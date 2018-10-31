# How do I...

This page answers common how-to questions that may come up when using AutoValue.
You should read and understand the [Introduction](index.md) first.

Questions specific to usage of the **builder option** are documented separately;
for this, start by reading [AutoValue with builders](builders.md).

## Contents

How do I...

*   ... [also generate a **builder** for my value class?](#builder)
*   ... [include `with-` methods on my value class for creating slightly
    **altered** instances?](#withers)
*   ... [use (or not use) JavaBeans-style name **prefixes**?](#beans)
*   ... [use **nullable** properties?](#nullable)
*   ... [perform other **validation**?](#validate)
*   ... [use a property of a **mutable** type?](#mutable_property)
*   ... [use a **custom** implementation of `equals`, etc.?](#custom)
*   ... [have multiple **`create`** methods, or name it/them
    differently?](#create)
*   ... [**ignore** certain properties in `equals`, etc.?](#ignore)
*   ... [have AutoValue also implement abstract methods from my
    **supertypes**?](#supertypes)
*   ... [also include **setter** (mutator) methods?](#setters)
*   ... [have one `@AutoValue` class **extend** another?](#inherit)
*   ... [keep my accessor methods **private**?](#private_accessors)
*   ... [expose a **constructor**, not factory method, as my public creation
    API?](#public_constructor)
*   ... [use AutoValue on an **interface**, not abstract class?](#interface)
*   ... [**memoize** ("cache") derived properties?](#memoize)

## <a name="builder"></a>... also generate a builder for my value class?

Please see [AutoValue with builders](builders.md).

## <a name="withers"></a>... include `with-` methods on my value class for creating slightly altered instances?

This is a somewhat common pattern among immutable classes. You can't have
setters, but you can have methods that act similarly to setters by returning a
new immutable instance that has one property changed.

To add a wither to your class, simply write the abstract method and AutoValue
will generate the concrete method for you.

```php
/**
 * @AutoValue
 */
abstract class Animal
{
  public static function create(String $name, int $numberOfLegs): self
  {
    return new AutoValue_Animal([
      'name' => $name,
      'numberOfLegs' => $numberOfLegs,
    ]);
  }

  abstract function name(): string;
  abstract function withName(string $name): self;
  abstract function numberOfLegs(): int;
  abstract function equals($value): bool;
}
```

Note that it's your free choice whether to make `withName` public or protected.

## <a name="beans"></a>... use (or not use) JavaBeans-style name prefixes?

Some developers prefer to name their accessors with a `get-` or `is-` prefix,
but would prefer that only the "bare" property name be used in `toString` and
for the generated constructor's parameter names.

AutoValue will do exactly this, but only if you are using these prefixes
*consistently*. In that case, it infers your intended property name by first
stripping the `get-` or `is-` prefix, then adjusting the case of what remains
using [lcfirst()](http://php.net/manual/en/function.lcfirst.php).

Note that, in keeping with the JavaBeans specification, the `is-` prefix is only
allowed on `boolean`-returning methods. `get-` is allowed on any type of
accessor.

## <a name="nullable"></a>... use nullable properties?

If you want to allow null values for a property, simply use PHP 7.1's nullable
parameter and return types where applicable. Example:

```php
/**
 * @AutoValue
 */
abstract class Foo
{
  static function create(?Bar $bar): self
  {
    return new AutoValue_Foo(['bar' => $bar]);
  }

  abstract function bar(): ?Bar;
}
```

## <a name="validate"></a>... perform other validation?

For precondition checks or pre-processing, just add them to your factory method:

```php
static function create(string $first, string $second): self
{
  assert(!empty($first));
  return new AutoValue_MyType(['first' => $first, 'second' => trim($second)]);
}
```

## <a name="mutable_property"></a>... use a property of a mutable type?

AutoValue classes are meant and expected to be immutable. But sometimes you
would want to take a mutable type and use it as a property. In these cases:

First, check if the mutable type has a corresponding immutable cousin. For
example, the `DateTime` has an immutable counterpart `DateTimeImmutable`. If so,
use the immutable type for your property, and only accept the mutable type
during construction:

```php
/**
 * @AutoValue
 */
abstract class DateTimeExample
{
  static function create(DateTime $date): self
  {
    return new AutoValue_DateTimeExample(['date' => DateTimeImmutable::fromMutable($date)]);
  }

  abstract function date(): DateTimeImmutable;
}
```

Note: this is a perfectly sensible practice, not an ugly workaround!

If there is no suitable immutable type to use, you'll need to proceed with
caution. Your static factory method should pass a *clone* of the passed object
to the generated constructor. Your accessor method should document a very loud
warning never to mutate the object returned.

```php
/**
 * @AutoValue
 */
abstract class MutableExample
{
  static function create(MutablePropertyType $ouch): self
  {
    // Replace `clone` below with the right copying code for this type
    return new AutoValue_MutableExample(['ouch' => clone $ouch]);
  }

  /**
   * Returns the ouch associated with this object; <b>do not mutate</b> the
   * returned object.
   */
  abstract function ouch(): MutablePropertyType;
}
```

Warning: this is an ugly workaround, not a perfectly sensible practice! Callers
can trivially break the invariants of the immutable class by mutating the
accessor's return value. An example where something can go wrong: AutoValue
objects can be used as keys in Maps.

## <a name="custom"></a>... use a custom implementation of `equals`, etc.?

Simply write your custom implementation; AutoValue will notice this and will
skip generating its own. Your hand-written logic will thus be inherited on the
concrete implementation class. We call this *underriding* the method.

Best practice: mark your underriding methods `final` to make it clear to future
readers that these methods aren't overridden by AutoValue.

## <a name="create"></a>... have multiple `create` methods, or name it/them differently?

Just do it! AutoValue doesn't actually care. This
[best practice item](practices.md#one_reference) may be relevant.

## <a name="ignore"></a>... ignore certain properties in `equals`?

Suppose your value class has an extra field that shouldn't be included in
`equals`.

If this is because it is a derived value based on other properties, see [How do
I memoize derived properties?](#memoize).

Otherwise, first make certain that you really want to do this. It is often, but
not always, a mistake. Remember that libraries will treat two equal instances as
absolutely *interchangeable* with each other. Whatever information is present in
this extra field could essentially "disappear" when you aren't expecting it, for
example when your value is stored and retrieved from certain collections.

If you're sure, here is how to do it:

```php
/**
 * @AutoValue
 */
abstract class IgnoreExample
{
  static function create(string $normalProperty, string $ignoredProperty): self
  {
    $ie = new AutoValue_IgnoreExample(['normalProperty' => $normalProperty]);
    $ie->ignoredProperty = $ignoredProperty;
    return $ie;
  }

  abstract function normalProperty(): string;

  private $ignoredProperty;

  final public function ignoredProperty(): string
  {
    return $this->ignoredProperty;
  }
}
```

## <a name="supertypes"></a>... have AutoValue also implement abstract methods from my supertypes?

AutoValue will recognize every abstract accessor method whether it is defined
directly in your own hand-written class or in a supertype.

## <a name="setters"></a>... also include setter (mutator) methods?

You can't; AutoValue only generates immutable value classes.

Note that giving value semantics to a mutable type is widely considered a
questionable practice in the first place. Equal instances of a value class are
treated as *interchangeable*, but they can't truly be interchangeable if one
might be mutated and the other not.
=
## <a name="inherit"></a>... have one `@AutoValue` class extend another?

This ability is intentionally not supported, because there is no way to do it
correctly. See *Effective Java, 2nd Edition* Item 8: "Obey the general contract
when overriding equals".

## <a name="private_accessors"></a>... keep my accessor methods private?

We're sorry. This is one of the rare and unfortunate restrictions AutoValue's
approach places on your API. Your accessor methods don't have to be *public*,
but they must be at least protected.

## <a name="public_constructor"></a>... expose a constructor, not factory method, as my public creation API?

We're sorry. This is one of the rare restrictions AutoValue's approach places on
your API. However, note that static factory methods are recommended over public
constructors by *Effective Java*, Item 1.

## <a name="interface"></a>... use AutoValue on an interface, not abstract class?

Interfaces are not allowed. The only advantage of interfaces we're aware of is
that you can omit `abstract` from the methods. That's not much. On the
other hand, you would lose the immutability guarantee, and you'd also invite
more of the kind of bad behavior described in [this best-practices
item](practices.md#simple). On balance, we don't think it's worth it.

## <a name="memoize"></a>... memoize ("cache") derived properties?

Sometimes your class has properties that are derived from the ones that
AutoValue implements. You'd typically implement them with a concrete method that
uses the other properties:

```php
/**
 * @AutoValue
 */
abstract class Foo
{
  abstract function barProperty(): Bar;

  function derivedProperty(): string
  {
    return someFunctionOf($this->barProperty());
  }
}
```

But what if `someFunctionOf(Bar)` is expensive? You'd like to calculate it only
one time, then cache and reuse that value for all future calls. Normally,
lazy initialization involves a bit of boilerplate.

Instead, just write the derived-property accessor method as above, and
annotate it with `@Memoized`. Then AutoValue will override that method to
return a stored value after the first call:

```php
/**
 * @AutoValue
 */
abstract class Foo
{
  abstract function barProperty(): Bar;

  /**
   * @Memoized
   */
  function derivedProperty(): string
  {
    return someFunctionOf($this->barProperty());
  }
}
```

Then your method will be called at most once.

The annotated method must have the usual form of an accessor method, and may not
be `abstract`, `final`, or `private`.

The stored value will not be used in the implementation of `equals`.