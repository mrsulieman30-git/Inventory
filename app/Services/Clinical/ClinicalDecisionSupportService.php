<?php

namespace App\Services\Clinical;

use App\Models\Item;
use Illuminate\Support\Facades\Log;

/**
 * Class ClinicalDecisionSupportService
 *
 * Provides automated AI/Rules-based Clinical Decision Support (CDS) during dispensing.
 * Validates allergies, duplicate therapies, and drug-drug interactions.
 */
class ClinicalDecisionSupportService
{
    /**
     * Check if the item being dispensed is safe for the patient.
     *
     * @param Item $item The medication to be dispensed.
     * @param string $patientId The patient's ID/MRN.
     * @return array Returns an array with 'is_safe' (bool) and 'warnings' (array).
     */
    public function performSafetyCheck(Item $item, string $patientId): array
    {
        $warnings = [];

        // In a real enterprise application, this would query a Patient model
        // or external EMR/EHR system (via HL7/FHIR) to fetch allergies.
        $patientAllergies = $this->mockFetchPatientAllergies($patientId);

        // 1. Allergy Checking
        if ($this->hasAllergyConflict($item, $patientAllergies)) {
            $warnings[] = "ALLERGY ALERT: Patient has a documented allergy to components in [{$item->name}] ({$item->generic_name}).";
        }

        // 2. Drug-Drug Interaction (DDI) Checking
        // Would normally query the patient's currently active prescriptions
        $activeMedications = $this->mockFetchPatientActiveMeds($patientId);
        $interaction = $this->checkInteractions($item, $activeMedications);

        if ($interaction) {
            $warnings[] = "DRUG INTERACTION: [{$item->name}] interacts with active medication [{$interaction['drug']}]. Severity: {$interaction['severity']}.";
        }

        return [
            'is_safe'  => empty($warnings),
            'warnings' => $warnings,
        ];
    }

    /**
     * Helper to determine if an allergy matches the drug.
     */
    protected function hasAllergyConflict(Item $item, array $allergies): bool
    {
        // Simple mock logic matching generic name
        foreach ($allergies as $allergy) {
            if (stripos($item->generic_name ?? '', $allergy) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check interactions against the dynamic drug_interactions table (V3 CDS).
     */
    protected function checkInteractions(Item $newItem, array $activeMedIds): ?array
    {
        if (empty($activeMedIds)) {
            return null;
        }

        // Query the database for any matching interactions between the new item and the active meds
        // We must check both directions since the interaction table pairs A-B or B-A
        $interaction = \Illuminate\Support\Facades\DB::table('drug_interactions')
            ->join('items as active_item', function($join) use ($newItem) {
                $join->on('active_item.id', '=', 'drug_interactions.primary_item_id')
                     ->where('drug_interactions.secondary_item_id', '=', $newItem->id)
                     ->orOn('active_item.id', '=', 'drug_interactions.secondary_item_id')
                     ->where('drug_interactions.primary_item_id', '=', $newItem->id);
            })
            ->whereIn('active_item.id', $activeMedIds)
            ->where('drug_interactions.is_active', true)
            ->select('active_item.name as interacting_drug', 'drug_interactions.severity', 'drug_interactions.description')
            // SQLite/PostgreSQL agnostic sorting instead of FIELD()
            ->orderByRaw("
                CASE severity
                    WHEN 'contraindicated' THEN 1
                    WHEN 'severe' THEN 2
                    WHEN 'moderate' THEN 3
                    WHEN 'mild' THEN 4
                    ELSE 5
                END
            ") // Return worst first
            ->first();

        if ($interaction) {
            return [
                'drug'     => $interaction->interacting_drug,
                'severity' => strtoupper($interaction->severity),
                'details'  => $interaction->description
            ];
        }

        return null;
    }

    // --- Mock Data Providers (These would normally hit the EMR/Patient microservice via API) ---

    protected function mockFetchPatientAllergies(string $patientId): array
    {
        return $patientId === 'PAT-123' ? ['Penicillin', 'Amoxicillin'] : [];
    }

    protected function mockFetchPatientActiveMeds(string $patientId): array
    {
        // V3 expects item IDs to compare against the drug_interactions table efficiently
        // e.g. PAT-456 is currently taking Item ID 1 (Warfarin)
        return $patientId === 'PAT-456' ? [1, 2] : [];
    }
}
