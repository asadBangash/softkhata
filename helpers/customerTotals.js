const Entry = require('../model/entry');
const Customer = require('../model/customer');

async function syncCustomerTotalsFromEntries(customerId, lastEntryType = '', lastEntry = '0') {
    const entries = await Entry.findByCustomerId(customerId);
    const total = entries.reduce((sum, item) => sum + parseInt(item.balance, 10), 0);
    const totalExpense = entries
        .filter((value) => value.entryType === 'income')
        .reduce((sum, item) => sum + parseInt(item.balance, 10), 0);
    const totalIncome = entries
        .filter((value) => value.entryType === 'expense')
        .reduce((sum, item) => sum + parseInt(item.balance, 10), 0);

    let latestType = lastEntryType;
    let latestBalance = lastEntry;
    if (entries.length > 0) {
        latestType = entries[0].entryType;
        latestBalance = entries[0].balance;
    } else {
        latestType = '';
        latestBalance = '0';
    }

    await Customer.updateTotals(customerId, {
        lastEntryType: latestType,
        lastEntry: latestBalance,
        totalBalance: String(total),
        totalExpense: String(totalExpense),
        totalIncome: String(totalIncome)
    });
}

module.exports = { syncCustomerTotalsFromEntries };
