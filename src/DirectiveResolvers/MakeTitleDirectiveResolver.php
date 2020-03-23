<?php
namespace Leoloso\ExamplesForPoP\DirectiveResolvers;

use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;

class MakeTitleDirectiveResolver extends MakeTitle_Version_0_2_0_DirectiveResolver
{
    public static function getPriorityToAttachClasses(): ?int
    {
        return null;
    }

    public function getSchemaDirectiveVersion(TypeResolverInterface $typeResolver): ?string
    {
        return null;
    }
}
