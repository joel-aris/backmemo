<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pharmacist;

final class MerkleRegistryService
{
    public const PROOF_VERSION = 'v1';

    public function __construct(private readonly MerkleTreeService $merkle) {}

    /**
     * @return array{merkle_root: ?string, merkle_valid: bool, merkle_proof_nodes: int, proof_version: string}
     */
    public function proofFor(Pharmacist $pharmacist): array
    {
        $hashes = Pharmacist::query()
            ->orderBy('id')
            ->pluck('verification_hash')
            ->all();

        $leaves = array_values(array_unique(array_map('strval', $hashes)));
        $tree = $this->merkle->build($hashes);
        $root = $tree['root'];
        $leaf = (string) $pharmacist->verification_hash;

        if ($root === null) {
            return [
                'merkle_root' => null,
                'merkle_valid' => false,
                'merkle_proof_nodes' => 0,
                'proof_version' => self::PROOF_VERSION,
            ];
        }

        $proof = count($leaves) > 1
            ? $this->merkle->generateProof($leaf, $leaves)
            : (in_array($leaf, $leaves, true) ? [] : null);

        $merkleValid = $proof !== null && $this->merkle->verify($leaf, (string) $root, $proof);

        return [
            'merkle_root' => $root,
            'merkle_valid' => $merkleValid,
            'merkle_proof_nodes' => $proof !== null ? count($proof) : 0,
            'proof_version' => self::PROOF_VERSION,
        ];
    }
}
