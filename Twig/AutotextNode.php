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
    public function __construct(Node $body, AbstractExpression $id = null, ArrayExpression $vars = null, $line = 0, $tag = null)
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
            ->write('ob_start();'.PHP_EOL)
            //->subcompile($this->getNode('body'))
            ->subcompile($node)
            ->write('echo $tmp;'.PHP_EOL)
            ->write('$body = ob_get_clean();'.PHP_EOL);

        if ($this->hasNode('id') && $this->getNode('id')) {
            $compiler->raw('$id = ')->subcompile($this->getNode('id'))->raw(';'.PHP_EOL);
        } else {
            $compiler->raw('$id = null;'.PHP_EOL);
        }

        if ($this->hasNode('vars') && $this->getNode('vars') instanceof ArrayExpression) {
            $compiler
                ->raw('$vars = ')
                ->subcompile($this->getNode('vars'))
                ->raw(';'.PHP_EOL);
        } else {
            $compiler->raw('$vars = [];'.PHP_EOL);
        }

        $compiler->write('echo $this->env->getExtension(\'Evirma\\Bundle\\AutotextBundle\\Twig\\AutotextExtension\')->autotext($body, $id, $vars);'.PHP_EOL);
    }
}
