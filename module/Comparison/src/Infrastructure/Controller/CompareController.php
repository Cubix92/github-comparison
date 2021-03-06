<?php

declare(strict_types=1);

namespace Comparison\Infrastructure\Controller;

use Comparison\Application\Exception\NotFoundRepositoryException;
use Comparison\Application\Service\CompareManager;
use Comparison\Domain\Exception\InvalidSlugException;
use Comparison\Infrastructure\Utils\GithubParser;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Swagger\Annotations as SWG;

/**
 * @SWG\Swagger(
 *      basePath="/api/v1",
 *      schemes={"http"},
 *      produces={"application/json"},
 *      consumes={"application/json"},
 *      @SWG\Info(title="Comparison API Documentation", version="1.0"),
 *      @SWG\Response(
 *          response="Compare",
 *          description="Compare",
 *          @SWG\Schema(
 *              @SWG\Property(property="status", type="string", example="success"),
 *              @SWG\Property(property="data", type="array", minLength=2, maxLength=2,
 *                  @SWG\Items(ref="$/definitions/Repository")
 *              ))
 *      ),
 *      @SWG\Response(
 *          response="Error",
 *          description="Error",
 *          @SWG\Schema(
 *              @SWG\Property(property="status", type="string", example="error"),
 *              @SWG\Property(property="message", type="string", example="Error message")
 *          )
 *      )
 * )
 */
class CompareController extends AbstractRestfulController
{
    private CompareManager $compareManager;

    private GithubParser $parserService;

    public function __construct(CompareManager $compareManager, GithubParser $parserService)
    {
        $this->compareManager = $compareManager;
        $this->parserService = $parserService;
    }

    /**
     * @SWG\Get(
     *     path="/compare",
     *     summary="Compare two public github repositories",
     *     tags={"Comparison"},
     *     @SWG\Parameter(name="compare", in="query", type="string", description="Link or slug to repository"),
     *     @SWG\Parameter(name="to", in="query", type="string", description="Link or slug to repository"),
     *     @SWG\Response(response=400, ref="$/responses/Error"),
     *     @SWG\Response(response=404, ref="$/responses/Error"),
     *     @SWG\Response(response=200, ref="$/responses/Compare")
     * )
     */
    public function compareAction(): JsonModel
    {
        $compare = $this->params()->fromQuery('compare', '');
        $to = $this->params()->fromQuery('to', '');

        try {
            $firstRepositorySlug = $this->parserService->parse($compare);
            $secondRepositorySlug = $this->parserService->parse($to);

            $result = $this->compareManager->compare($firstRepositorySlug, $secondRepositorySlug);
        } catch (NotFoundRepositoryException $e) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (InvalidSlugException $e) {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

        return new JsonModel([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
