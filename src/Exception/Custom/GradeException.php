<?php

namespace App\Exception\Custom;

use App\Exception\AppException;

class GradeException extends AppException
{
    public const INVALID_DATA = 1001;
    public const NOT_FOUND = 1002;
    public const DUPLICATE = 1003;

    public static function invalidData(array $errors): self
    {
        return new self(
            'Invalid grade data provided',
            ['errors' => $errors],
            self::INVALID_DATA
        );
    }

    public static function notFound(int $gradeId): self
    {
        return new self(
            sprintf('Grade with ID %d not found', $gradeId),
            ['grade_id' => $gradeId],
            self::NOT_FOUND
        );
    }
}