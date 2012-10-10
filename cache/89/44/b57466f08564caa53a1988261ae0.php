<?php

/* base.html */
class __TwigTemplate_8944b57466f08564caa53a1988261ae0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    ";
        // line 7
        if (isset($context["var"])) { $_var_ = $context["var"]; } else { $_var_ = null; }
        echo twig_escape_filter($this->env, $_var_, "html", null, true);
        echo "
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "base.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  25 => 7,  17 => 1,);
    }
}
