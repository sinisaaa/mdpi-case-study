<?php

declare(strict_types=1);

namespace App\Tests;

use App\Enum\BookSortTypeEnum;
use App\Service\Validation\BookSearchRequestValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BookSearchRequestValidatorTest extends TestCase
{
    private BookSearchRequestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new BookSearchRequestValidator();
    }

    public function testValidRequestReturnsNoErrors(): void
    {
        $request = new Request([
            'sort' => BookSortTypeEnum::TITLE_ASC->value,
            'pagination' => [
                'page' => 1,
                'limit' => 10,
                'pagination_type' => 'offset',
                'cursor' => '',
                'field' => '',
                'direction' => 'asc',
            ],
        ]);
        $this->assertSame([], $this->validator->validate($request));
    }

    public function testInvalidPaginationParameters(): void
    {
        $request = new Request([
            'pagination' => [
                'page' => 0,
                'limit' => 0,
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid pagination parameters', $errors);
    }

    public function testLimitGreaterThanMax(): void
    {
        $request = new Request([
            'pagination' => [
                'limit' => 101,
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Limit cannot be greater than 100', $errors);
    }

    public function testInvalidSortType(): void
    {
        $request = new Request([
            'sort' => 'invalid_sort',
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid sort type', $errors);
    }

    public function testInvalidPaginationType(): void
    {
        $request = new Request([
            'pagination' => [
                'pagination_type' => 'invalid_type',
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid pagination type', $errors);
    }

    public function testInvalidCursorValue(): void
    {
        $request = new Request([
            'pagination' => [
                'cursor' => 123,
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid cursor value', $errors);
    }

    public function testInvalidFieldValue(): void
    {
        $request = new Request([
            'pagination' => [
                'field' => ['not', 'a', 'string'],
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid field value', $errors);
    }

    public function testInvalidDirectionValue(): void
    {
        $request = new Request([
            'pagination' => [
                'direction' => 'sideways',
            ],
        ]);
        $errors = $this->validator->validate($request);
        $this->assertContains('Invalid direction value', $errors);
    }

}
