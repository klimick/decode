<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeVisit;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\InferUnion;
use Klimick\Decode\Decoder\UnionInstance;
use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Common\GetMethodReturnType;
use Klimick\PsalmDecode\Common\MetaMixinGenerator;
use Klimick\PsalmDecode\Plugin;
use Psalm\CodeLocation;
use Psalm\Internal\MethodIdentifier;
use Psalm\Internal\Type\TypeAlias\ClassTypeAlias;
use Psalm\Issue\InvalidClass;
use Psalm\Issue\InvalidReturnType;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\Assertion;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Storage\MethodStorage;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TCallable;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TTypeAlias;
use Psalm\Type\Union;
use function array_key_exists;
use function Fp\Cast\asList;
use function Fp\Collection\filter;
use function Fp\Evidence\proveTrue;
use function strtolower;

final class InferUnionAfterClassLikeVisit implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $storage = $event->getStorage();

            yield proveTrue(PsalmApi::$classlikes->classImplements($storage, InferUnion::class));

            $cases = yield GetMethodReturnType::from(
                    class: $storage->name,
                    method_name: 'union',
                    deps: [$storage->name],
                )
                ->flatMap(fn($type) => DecoderType::getGeneric($type))
                ->orElse(function() use ($storage, $event) {
                    IssueBuffer::accepts(
                        new InvalidClass(
                            'Unable to analyze type info for ' . $storage->name,
                            new CodeLocation($event->getStatementsSource(), $event->getStmt()),
                            $storage->name
                        )
                    );
                    return Option::none();
                });

            self::addTypeAlias($storage, $cases);
            self::addUnionMethod($storage);
            self::removeMetaMixin($storage);

            if (PsalmApi::$classlikes->isTraitUsed($storage, UnionInstance::class)) {
                self::addValueProperty($storage, $cases);
                self::addMatchMethod($storage, $cases);
                self::addIsMethod($storage);
                self::addTypeMethod($storage);
            }

            if (Plugin::isMixinGenerationEnabled()) {
                MetaMixinGenerator::createUnionMetaMixin($storage, $cases);
            }
        });
    }

    private static function addTypeAlias(ClassLikeStorage $storage, Union $cases): void
    {
        $replacement_atomic_types = asList($cases->getAtomicTypes());
        $storage->type_aliases[self::typeAlias($storage)->alias_name] = new ClassTypeAlias($replacement_atomic_types);
    }

    private static function typeAlias(ClassLikeStorage $storage): TTypeAlias
    {
        return new TTypeAlias($storage->name, PsalmApi::$classlikes->toShortName($storage) . 'Union');
    }

    private static function addValueProperty(ClassLikeStorage $storage, Union $cases): void
    {
        $storage->pseudo_property_get_types['$value'] = $cases;
        $storage->sealed_properties = true;
        $storage->sealed_methods = true;
    }

    private static function addUnionMethod(ClassLikeStorage $storage): void
    {
        if (!array_key_exists('union', $storage->methods) || null === $storage->methods['union']->signature_return_type) {
            return;
        }

        $atomics = asList($storage->methods['union']->signature_return_type->getAtomicTypes());

        if (count($atomics) > 1 && null !== $storage->methods['union']->location) {
            $storage->docblock_issues[] = new InvalidReturnType(
                message: "Method 'union' must have only one return type! (UnionDecoder or TaggedUnionDecoder)",
                code_location: $storage->methods['union']->location,
            );

            return;
        }

        $storage->methods['union']->return_type = DecoderType::create($atomics[0]->getId(), self::typeAlias($storage));
    }

    private static function addMatchMethod(ClassLikeStorage $storage, Union $cases): void
    {
        $name_lc = 'match';

        $method = new MethodStorage();
        $method->cased_name = $name_lc;
        $method->allow_named_arg_calls = false;
        $method->return_type = self::matchReturnType($storage);
        $method->template_types = [
            'TMatch' => [self::matchTemplateId($storage) => Type::getMixed()],
        ];

        $params = [];

        foreach (asList($cases->getAtomicTypes()) as $offset => $atomic) {
            $params[] = self::toMatcherParam("param{$offset}", $atomic, $storage);
        }

        /** @psalm-suppress InternalMethod */
        $method->setParams($params);
        $method->required_param_count = count($params);

        $storage->declaring_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->appearing_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->methods[$name_lc] = $method;
    }

    private static function toMatcherParam(string $name, Atomic $type, ClassLikeStorage $storage): FunctionLikeParameter
    {
        return new FunctionLikeParameter(
            name: $name,
            by_ref: false,
            type: new Union([
                new TCallable(
                    params: [
                        new FunctionLikeParameter(name: 'value', by_ref: false, type: new Union([$type])),
                    ],
                    return_type: self::matchReturnType($storage),
                ),
            ]),
            is_optional: false,
        );
    }

    private static function matchReturnType(ClassLikeStorage $storage): Type\Union
    {
        return new Union([
            new Atomic\TTemplateParam(
                param_name: 'TMatch',
                extends: Type::getMixed(),
                defining_class: self::matchTemplateId($storage),
            ),
        ]);
    }

    private static function matchTemplateId(ClassLikeStorage $storage): string
    {
        return 'fn-' . strtolower($storage->name) . '::' . 'match';
    }

    private static function addIsMethod(ClassLikeStorage $storage): void
    {
        $name_lc = 'is';
        $class_name_lc = strtolower($storage->name);

        $method = new MethodStorage();
        $method->cased_name = $name_lc;
        $method->if_true_assertions[] = new Assertion('$this->value', [['T']]);
        $method->return_type = Type::getBool();
        $method->template_types = [
            'T' => ["fn-{$class_name_lc}::{$name_lc}" => Type::getObject()],
        ];

        $params = [
            new FunctionLikeParameter(
                name: 'class',
                by_ref: false,
                type: new Union([
                    new Atomic\TTemplateParamClass('T', 'object', null, "fn-{$class_name_lc}::{$name_lc}")
                ]),
                is_optional: false,
            ),
        ];

        /** @psalm-suppress InternalMethod */
        $method->setParams($params);
        $method->required_param_count = count($params);

        $storage->declaring_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->appearing_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->methods[$name_lc] = $method;
    }

    private static function addTypeMethod(ClassLikeStorage $storage): void
    {
        $name_lc = 'type';

        $method = new MethodStorage();
        $method->cased_name = $name_lc;
        $method->is_static = true;
        $method->return_type = DecoderType::create(DecoderInterface::class, new TNamedObject($storage->name));

        $storage->declaring_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->appearing_method_ids[$name_lc] = new MethodIdentifier($storage->name, $name_lc);
        $storage->methods[$name_lc] = $method;
    }

    private static function removeMetaMixin(ClassLikeStorage $from): void
    {
        $from->namedMixins = filter($from->namedMixins, fn($t) => $t->value !== "{$from->name}MetaMixin");
    }
}
