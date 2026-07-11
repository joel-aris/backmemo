<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class MerkleTreeService
{
    public function build(array $hashes): array
    {
        if (empty($hashes)) {
            return ['root' => null, 'leaves' => []];
        }

        $leaves = array_values(array_unique(array_map('strval', $hashes)));
        $levels = [];

        while (count($leaves) > 1) {
            $levels[] = $leaves;
            $next = [];

            for ($i = 0; $i < count($leaves); $i += 2) {
                $left = $leaves[$i];
                $right = $leaves[$i + 1] ?? $left;
                $next[] = hash('sha256', $left . $right);
            }

            $leaves = $next;
        }

        return [
            'root' => $leaves[0] ?? null,
            'leaves' => $levels[0] ?? [],
            'levels' => $levels,
        ];
    }

    public function verify(string $leaf, string $root, array $proof): bool
    {
        if ($root === null || $root === '') {
            return false;
        }

        $current = $leaf;

        foreach ($proof as $pair) {
            $sibling = $pair['hash'];
            $position = $pair['position'];

            if ($position === 'left') {
                $current = hash('sha256', $sibling . $current);
            } else {
                $current = hash('sha256', $current . $sibling);
            }
        }

        return hash_equals($current, $root);
    }

    public function generateProof(string $leaf, array $leaves): ?array
    {
        $index = array_search($leaf, $leaves, true);

        if ($index === false) {
            return null;
        }

        $proof = [];
        $currentLevel = $leaves;

        while (count($currentLevel) > 1) {
            $nextLevel = [];
            $siblingIndex = $index % 2 === 0 ? $index + 1 : $index - 1;

            if (isset($currentLevel[$siblingIndex])) {
                $proof[] = [
                    'hash' => $currentLevel[$siblingIndex],
                    'position' => $index % 2 === 0 ? 'right' : 'left',
                ];
            }

            for ($i = 0; $i < count($currentLevel); $i += 2) {
                $left = $currentLevel[$i];
                $right = $currentLevel[$i + 1] ?? $left;
                $nextLevel[] = hash('sha256', $left . $right);
            }

            $currentLevel = $nextLevel;
            $index = (int) floor($index / 2);
        }

        return $proof;
    }
}
