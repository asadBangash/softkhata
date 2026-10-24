function toIsoDate(value) {
    if (!value) {
        return null;
    }
    if (value instanceof Date) {
        return value.toISOString();
    }
    return String(value);
}

function formatUser(row, options = {}) {
    if (!row) {
        return null;
    }
    const doc = {
        _id: String(row.id),
        name: row.name,
        email: row.email,
        number: Number(row.number) || row.number,
        businessName: row.business_name,
        businessCategory: row.business_category,
        createdAt: toIsoDate(row.created_at),
        updatedAt: toIsoDate(row.updated_at),
        __v: 0
    };
    if (options.includePassword) {
        doc.password = row.password;
    }
    return doc;
}

function formatCustomer(row) {
    if (!row) {
        return null;
    }
    return {
        _id: String(row.id),
        user: String(row.user_id),
        name: row.name,
        number: row.number,
        lastEntry: row.last_entry,
        lastEntryType: row.last_entry_type,
        totalBalance: row.total_balance,
        totalExpense: row.total_expense,
        totalIncome: row.total_income,
        createdAt: toIsoDate(row.created_at),
        updatedAt: toIsoDate(row.updated_at),
        __v: 0
    };
}

function formatEntry(row) {
    if (!row) {
        return null;
    }
    return {
        _id: String(row.id),
        userId: String(row.user_id),
        customerId: String(row.customer_id),
        description: row.description,
        balance: row.balance,
        entryType: row.entry_type,
        time: row.time,
        __v: 0
    };
}

module.exports = {
    formatUser,
    formatCustomer,
    formatEntry
};
