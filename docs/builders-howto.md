# How do I... (Builder edition)

This page answers common how-to questions that may come up when using AutoValue
**with the builder option**. You should read and understand [AutoValue with
builders](builders.md) first.

If you are not using a builder, see [Introduction](index.md) and
[How do I...](howto.md) instead.

## Contents

How do I...

*   ... [use (or not use) `set` **prefixes**?](#beans)
*   ... [use different **names** besides
    `builder()`/`Builder`/`build()`?](#build_names)
*   ... [specify a **default** value for a property?](#default)
*   ... [initialize a builder to the same property values as an **existing**
    value instance](#to_builder)
*   ... [**validate** property values?](#validate)

## <a name="beans"></a>... use (or not use) `set` prefixes?

Just as you can choose whether to use JavaBeans-style names for property getters
(`getFoo()` or just `foo()`) in your value class, you have the same choice for
setters in builders too (`setFoo(value)` or just `foo(value)`). As with getters,
you must use these prefixes consistently or not at all.

Using `get`/`is` prefixes for getters and using the `set` prefix for setters are
independent choices. For example, it is fine to use the `set` prefixes on all
your builder methods, but omit the `get`/`is` prefixes from all your accessors.

Here is the `Animal` example using `get` prefixes but not `set` prefixes:

```php
/**
 * @AutoValue
 */
abstract class Animal
{
  abstract function getName(): string;
  abstract function getNumberOfLegs(): int;

  static function builder(): AnimalBuilder
  {
    return new AutoValue_AnimalBuilder();
  }
}

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

## <a name="build_names"></a>... use different names besides `builder()`/`Builder`/`build()`?

Use whichever names you like; AutoValue doesn't actually care.

(We would gently recommend these names as conventional.)

## <a name="default"></a>... specify a default value for a property?

What should happen when a caller does not supply a value for a property before
calling `build()`? If the property in question is [nullable](howto.md#nullable),
it will simply default to `null` as you would expect. But if is not nullable,
then `build()` will throw an exception.

But this presents a problem, since one of the main *advantages* of a builder in
the first place is that callers can specify only the properties they care about!

The solution is to provide a default value for such properties. Fortunately this
is easy: just set it on the newly-constructed builder instance before returning
it from the `builder()` method.

Here is the `Animal` example with the default number of legs being 4:

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
    return new AutoValue_AnimalBuilder()->setNumberOfLegs(4);
  }
}
```

Occasionally you may want to supply a default value, but only if the property is
not set explicitly. This is covered in the section on
[normalization](#normalize).

## <a name="to_builder"></a>... initialize a builder to the same property values as an existing value instance

Suppose your caller has an existing instance of your value class, and wants to
change only one or two of its properties. Of course, it's immutable, but it
would be convenient if they could easily get a `Builder` instance representing
the same property values, which they could then modify and use to create a new
value instance.

To give them this ability, just add an abstract `toBuilder` method, returning
your abstract builder type, to your value class. AutoValue will implement it.

```php
  abstract function toBuilder(): FooBuilder;
```

## <a name="validate"></a>... validate property values?

Validating properties is a little less straightforward than it is in the
[non-builder case](howto.md#validate).

What you need to do is *split* your "build" method into two methods:

*   the non-visible, abstract method that AutoValue implements
*   and the visible, *concrete* method you provide, which calls the generated
    method and performs validation.

We recommend naming these methods `autoBuild` and `build`, but any names will
work. It ends up looking like this:

```php
/**
 * @AutoValue\Builder
 */
abstract class AnimalBuilder {
  abstract function name(string $value): self;
  abstract function numberOfLegs(int $value): self;
  
  function build(): Animal
  {
    $animal = $this->autoBuild();
    assert($animal->numberOfLegs() >= 0, "Negative legs");
    return $animal;
  }
  
  protected abstract function autoBuild(): Animal;
}
```

<!--
## <a name="normalize"></a>... normalize (modify) a property value at `build` time?

Suppose you want to convert the animal's name to lower case.

You'll need to add a *getter* to your builder, as shown:

```java
@AutoValue
public abstract class Animal {
  public abstract String name();
  public abstract int numberOfLegs();

  public static Builder builder() {
    return new AutoValue_Animal.Builder();
  }

  @AutoValue.Builder
  public abstract static class Builder {
    public abstract Builder setName(String value);
    public abstract Builder setNumberOfLegs(int value);

    abstract String name(); // must match method name in Animal

    abstract Animal autoBuild(); // not public

    public Animal build() {
      setName(name().toLowerCase());
      return autoBuild();
    }
  }
}
```

The getter in your builder must have the same signature as the abstract property
accessor method in the value class. It will return the value that has been set
on the `Builder`. If no value has been set for a
non-[nullable](howto.md#nullable) property, `IllegalStateException` is thrown.

Getters should generally only be used within the `Builder` as shown, so they are
not public.

As an alternative to returning the same type as the property accessor method,
the builder getter can return an Optional wrapping of that type. This can be
used if you want to supply a default, but only if the property has not been set.
(The [usual way](#default) of supplying defaults means that the property always
appears to have been set.) For example, suppose you wanted the default name of
your Animal to be something like "4-legged creature", where 4 is the
`numberOfLegs()` property. You might write this:

```java
@AutoValue
public abstract class Animal {
  public abstract String name();
  public abstract int numberOfLegs();

  public static Builder builder() {
    return new AutoValue_Animal.Builder();
  }

  @AutoValue.Builder
  public abstract static class Builder {
    public abstract Builder setName(String value);
    public abstract Builder setNumberOfLegs(int value);

    abstract Optional<String> name();
    abstract int numberOfLegs();

    abstract Animal autoBuild(); // not public

    public Animal build() {
      if (!name().isPresent()) {
        setName(numberOfLegs() + "-legged creature");
      }
      return autoBuild();
    }
  }
}
```

Notice that this will throw `IllegalStateException` if the `numberOfLegs`
property hasn't been set either.

The Optional wrapping can be any of the Optional types mentioned in the
[section](#optional) on `Optional` properties. If your property has type `int`
it can be wrapped as either `Optional<Integer>` or `OptionalInt`, and likewise
for `long` and `double`.

## <a name="accumulate"></a>... let my builder *accumulate* values for an array-valued property (not require them all at once)?

You can achieve this by writing a concrete `addFoo()` method

```java
@AutoValue
public abstract class Animal {
  public abstract String name();
  public abstract int numberOfLegs();
  public abstract ImmutableSet<String> countries();

  public static Builder builder() {
    return new AutoValue_Animal.Builder();
  }

  @AutoValue.Builder
  public abstract static class Builder {
    public abstract Builder setName(String value);
    public abstract Builder setNumberOfLegs(int value);

    abstract ImmutableSet.Builder<String> countriesBuilder();
    public Builder addCountry(String value) {
      countriesBuilder().add(value);
      return this;
    }

    public abstract Animal build();
  }
}
```

Now the caller can do this:

```java
  // This DOES work!
  Animal dog = Animal.builder()
      .setName("dog")
      .setNumberOfLegs(4)
      .addCountry("Guam")
      .addCountry("Laos") // however many times needed
      .build();
```

### <a name="collection_both"></a>... offer both accumulation and set-at-once methods for the same collection-valued property?

You can have both. If the caller uses `setFoos` after `foosBuilder` has been
called, an unchecked exception will be thrown.

## <a name="nested_builders"></a>... access nested builders while building?

Often a property of an `@AutoValue` class is itself an immutable class,
perhaps another `@AutoValue`. In such cases your builder can expose a builder
for that nested class.

Suppose the `Animal` class has a property of type `Species`:

```php
/**
 * @AutoValue
 */
abstract class Animal
{
  abstract function getName(): string;
  abstract function getSpecies(): Species;

  static function builder(): AnimalBuilder
  {
    return new AutoValue_AnimalBuilder();
  }
}

/**
 * @AutoValue\Builder
 */
abstract class AnimalBuilder
{
  abstract function name(string $value): self;
  abstract function speciesBuilder(): SpeciesBuilder;
  abstract function build(): Animal;
}

/**
 * @AutoValue
 */
abstract class Species {
  abstract String genus();
  abstract String epithet();

  static function builder(): SpeciesBuilder {
    return new AutoValue_SpeciesBuilder();
  }
}

/**
 * @AutoValue\Builder
 */
abstract class SpeciesBuilder
{
  abstract setGenus(string $genus): self;
  abstract setEpithet(string $epithet): self;
  abstract function build(): Species;
}
```

Now you can access the builder of the nested `Species` while you are building
the `Animal`:

```java
  $catBuilder = Animal::builder()
      ->setName("cat");
  catBuilder.speciesBuilder()
      .setGenus("Felis")
      .setEpithet("catus");
  Animal cat = catBuilder.build();
```

Although the nested class in the example (`Species`) is also an `@AutoValue`
class, it does not have to be. For example, it could be a [protobuf]. The
requirements are:

* The nested class must have a way to make a new builder. This can be
  `new Species.Builder()`, or `Species.builder()`, or `Species.newBuilder()`.

* There must be a way to build an instance from the builder: `Species.Builder`
  must have a method `Species build()`.

* If there is a need to convert `Species` back into its builder, then `Species`
  must have a method `Species.Builder toBuilder()`.

  In the example, if `Animal` has an abstract [`toBuilder()`](#to_builder)
  method then `Species` must also have a `toBuilder()` method. That also applies
  if there is an abstract `setSpecies` method in addition to the
  `speciesBuilder` method.

There are no requirements on the name of the builder class. Instead of
`Species.Builder`, it could be `Species.Factory` or `SpeciesBuilder`.

If `speciesBuilder()` is never called then the final `species()` property will
be set as if by `speciesBuilder().build()`. In the example, that would result
in an exception because the required properties of `Species` have not been set.


[protobuf]: https://developers.google.com/protocol-buffers/docs/reference/java-generated#builders
-->