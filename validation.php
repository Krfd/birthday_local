<?php

// VALIDATION FROM responses TO master jums table
"SELECT *
FROM responses u
WHERE EXISTS (
    SELECT 1
    FROM ReferenceData r
    WHERE 
        r.invoice_number = u.invoice_number
        AND r.branch = u.branch
        AND r.first_name = u.first_name
        AND r.last_name = u.last_name
);";


// CHECK FOR DUPLICATE INVOICE ACROSS BRANCHES

// Even if invoice numbers are duplicated across branches in the reference data, 
// only insert records that haven’t already been inserted into ValidatedData.

"SELECT *
FROM responses u
WHERE EXISTS (
    SELECT 1
    FROM ReferenceData r
    WHERE 
        r.invoice_number = u.invoice_number
        AND r.branch = u.branch
        AND r.first_name = u.first_name
        AND r.last_name = u.last_name
)
AND NOT EXISTS (
    SELECT 1
    FROM raffle_entries v
    WHERE 
        v.invoice_number = u.invoice_number
        AND v.branch = u.branch
);";



// INSERT ONLY VALID, NON-DUPLICATE RECORDS

"INSERT INTO raffle_entries (branch, first_name, last_name, invoice_number)
SELECT u.branch, u.first_name, u.last_name, u.invoice_number
FROM responses u
WHERE EXISTS (
    SELECT 1
    FROM ReferenceData r
    WHERE 
        r.invoice_number = u.invoice_number
        AND r.branch = u.branch
        AND r.first_name = u.first_name
        AND r.last_name = u.last_name
)
AND NOT EXISTS (
    SELECT 1
    FROM raffle_entries v
    WHERE 
        v.invoice_number = u.invoice_number
        AND v.branch = u.branch
);";
