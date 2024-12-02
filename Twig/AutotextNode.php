<?php

declare(strict_types=1);

namespace Evirma\Bundle\AutotextBundle\Twig;

use Twig\Compiler;
use Twig\Node\CaptureNode;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;

class AutotextNode extends Node
{
    public function __construct(Node $body, ?AbstractExpression $id = null, ?ArrayExpression $vars = null, $line = 0, $tag = null)
    {
        $nodes = array('body' => $body);

        if (null !== $id) {
            $nodes['id'] = $id;
        }

        if (null !== $vars) {
            $nodes['vars'] = $vars;
        }

        parent::__construct($nodes, [], $line, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $node = new CaptureNode($this->getNode('body'), $this->getNode('body')->lineno, $this->getNode('body')->tag);
        $node->setAttribute('with_blocks', true);

        $compiler
            ->addDebugInfo($this)
            ->subcompile($node);

        if ($this->hasNode('id') && $this->getNode('id')) {
            $compiler->raw('$id = ')->subcompile($this->getNode('id'))->raw(';');
        } else {
            $compiler->raw('$id = null;');
        }

        if ($this->hasNode('vars') && $this->getNode('vars') instanceof ArrayExpression) {
            $compiler
                ->raw('$vars = ')
                ->subcompile($this->getNode('vars'))
                ->raw(';');
        } else {
            $compiler->raw('$vars = [];');
        }

        $compiler->write('echo $this->env->getExtension(\'Evirma\\Bundle\\AutotextBundle\\Twig\\AutotextExtension\')->autotext($tmp, $id, $vars);'.PHP_EOL);
    }
}
