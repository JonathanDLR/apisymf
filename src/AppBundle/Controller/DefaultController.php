<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Article;
use AppBundle\Representation\Articles;
use Symfony\Component\Validator\ConstraintViolationList;
use AppBundle\Exception\ResourceValidationException;

class DefaultController extends FOSRestController
{
    /**
     * @Rest\Get(
     *          path = "/articles/{id}",
     *          name = "article_show",
     *          requirements = {"id"="\d+"}
     * )
     * @Rest\View()
     */
    public function showAction(Article $article)
    {
        return $article;
    }

    /**
     * @Rest\Post(
     *          path = "/articles/create",
     *          name = "article_create"
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("article",
     *                 converter="fos_rest.request_body",
     *                 options={
     *                      "validator"={ "groups"="Create" }
     *                 }
     * )
     */
    public function createAction(Article $article, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = "Error: ";

            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $article;
    }

    /**
     * @Rest\Get("/articles/list", name="article_list")
     * @Rest\QueryParam(
     *      name = "keyword",
     *      requirements = "[a-zA-Z0-9]",
     *      nullable = true,
     *      description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *      name = "order",
     *      requirements = "asc|desc",
     *      default = "asc",
     *      description="Sort order"
     * )
     * @Rest\QueryParam(
     *      name = "limit",
     *      requirements = "\d+",
     *      default = "15",
     *      description="Max number per page"
     * )
     * @Rest\QueryParam(
     *      name = "offset",
     *      requirements = "\d+",
     *      default = "0",
     *      description="page"
     * )
     * @Rest\View(
     * 
     * )
     * @param ParamFetcherInterface $paramFetcher
     * @return mixed
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository('AppBundle:Article')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return iterator_to_array($pager);
    }

    /**
     * @Rest\Put(
     *      path = "/articles/update/{id}",
     *      name = "article_update",
     *      requirements = {"id"="d\+"}
     * )
     * @Rest\View(StatusCode = 200)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function updateAction($id, Article $article)
    {
        $article = $this->getDoctrine()->getRepository('AppBundle:Article')->find($id);

        if (empty($article))
        {
            throw new Exception("Cet article n'existe pas");
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($article);
        $em->flush();

        return $article;
    }
}
