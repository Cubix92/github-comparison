<?php

declare(strict_types=1);

namespace Comparison\Infrastructure\Utils;

use Comparison\Domain\ValueObject\RepositorySlug;
use Laminas\Uri\Exception\InvalidArgumentException;
use Comparison\Application\Exception\InvalidArgumentException as ParserInvalidArgument;
use Laminas\Uri\Uri;
use Laminas\Uri\UriFactory;

class GithubParser
{
    public function parse(string $parameter): RepositorySlug
    {
        if (!$parameter) {
            throw new ParserInvalidArgument(
                "Passing parameter is empty."
            );
        }

        $filteredParameter = strip_tags(trim($parameter));

        try {
            /** @var Uri $uri */
            $uri = UriFactory::factory($filteredParameter);
        } catch (InvalidArgumentException $e) {
            throw new ParserInvalidArgument(
                "'$parameter' is invalid. Please correct this parameter and will send it again."
            );
        }

        $slug = trim($uri->getPath(), '/');

        if (!$slug) {
            throw new ParserInvalidArgument(
                "'$parameter' is invalid. Please correct this parameter and will send it again."
            );
        }

        return new RepositorySlug($slug);
    }
}
