<<<<<<< HEAD
// admin-dashboard.js

// Global Variables
const apiBaseUrl = 'http://localhost/OASIS/html/'; // Adjust as per your server setup

// Utility Functions
const showLoading = () => document.getElementById('loading-overlay')?.classList.remove('hidden');
const hideLoading = () => document.getElementById('loading-overlay')?.classList.add('hidden');
const showError = (message) => {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
};

// Excel Export Function (unchanged)
function exportToExcel(fullData) {
    const wb = XLSX.utils.book_new();
    function formatSheet(sheet, data) {
        const colWidths = data[0].map((_, colIndex) =>
            Math.max(...data.map(row => String(row[colIndex] || '').length)) + 2
        );
        sheet['!cols'] = colWidths.map(w => ({ wch: w }));
        sheet['!rows'] = data.map(() => ({ hpt: 20, wrapText: true }));
    }
    const statsData = [
        ['Dashboard Stats'],
        ['Total Students', fullData.stats.totalStudents],
        ['Overdue Accounts', fullData.stats.overdueAccounts],
        ['Outstanding Balance', `₱${fullData.stats.outstandingBalance.toLocaleString()}`],
        ['Revenue', `₱${fullData.stats.revenue.toLocaleString()}`]
    ];
    const statsSheet = XLSX.utils.aoa_to_sheet(statsData);
    formatSheet(statsSheet, statsData);
    XLSX.utils.book_append_sheet(wb, statsSheet, 'Stats');
=======
// Global Variables
const apiBaseUrl = 'http://localhost/OASIS/html/'; // Corrected path
const CURRENT_DATE = 'March 13, 2025'; // System-provided current date

// Utility Functions
const showLoading = () => document.getElementById('loading-overlay')?.classList.remove('hidden');
const hideLoading = () => document.getElementById('loading-overlay')?.classList.add('hidden');
const showError = (message) => {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
};

// Excel Export Function
function exportToExcel(fullData) {
    const wb = XLSX.utils.book_new();

    function formatSheet(sheet, data) {
        const colWidths = data[0].map((_, colIndex) =>
            Math.max(...data.map(row => String(row[colIndex] || '').length)) + 2
        );
        sheet['!cols'] = colWidths.map(w => ({ wch: w }));
        sheet['!rows'] = data.map(() => ({ hpt: 20, wrapText: true }));
    }

    const statsData = [
        ['Dashboard Stats'],
        ['Total Students', fullData.stats.totalStudents],
        ['Overdue Accounts', fullData.stats.overdueAccounts],
        ['Outstanding Balance', `₱${fullData.stats.outstandingBalance.toLocaleString()}`],
        ['Revenue', `₱${fullData.stats.revenue.toLocaleString()}`]
    ];
    const statsSheet = XLSX.utils.aoa_to_sheet(statsData);
    formatSheet(statsSheet, statsData);
    XLSX.utils.book_append_sheet(wb, statsSheet, 'Stats');

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const distData = [['Unpaid Fees Distribution'], ...Object.entries(fullData.distributionData).map(([type, amount]) => [type, `₱${amount.toLocaleString()}`])];
    const distSheet = XLSX.utils.aoa_to_sheet(distData);
    formatSheet(distSheet, distData);
    XLSX.utils.book_append_sheet(wb, distSheet, 'Unpaid Fees');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const trendData = [['Payment Trend'], ['Date', 'Amount'], ...fullData.trendData.dates.map((date, i) => [date, `₱${fullData.trendData.amounts[i].toLocaleString()}`])];
    const trendSheet = XLSX.utils.aoa_to_sheet(trendData);
    formatSheet(trendSheet, trendData);
    XLSX.utils.book_append_sheet(wb, trendSheet, 'Payment Trend');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const historyData = [['History'], ['Receipt Number', 'Transaction Date', 'Amount Paid', 'Fees'], ...fullData.history.map(h => [h.receiptnumber, h.transactiondate, `₱${Number(h.amountpaid).toLocaleString()}`, h.fees])];
    const historySheet = XLSX.utils.aoa_to_sheet(historyData);
    formatSheet(historySheet, historyData);
    XLSX.utils.book_append_sheet(wb, historySheet, 'History');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const miscData = [['Miscellaneous Fees'], ['ID', 'Fee', 'Due Date', 'Amount', 'Status'], ...fullData.misc.map(m => [m.id, m.fee, m.duedate, `₱${Number(m.amount).toLocaleString()}`, m.status])];
    const miscSheet = XLSX.utils.aoa_to_sheet(miscData);
    formatSheet(miscSheet, miscData);
    XLSX.utils.book_append_sheet(wb, miscSheet, 'Misc');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const studentsData = [['Students'], ['Student Number', 'First Name', 'Last Name', 'Middle Name', 'Level', 'Payment Plan', 'Parent ID'], ...fullData.students.map(s => [s.studentnumber, s.firstname, s.lastname, s.middlename, s.level, s.paymentplan, s.parent_id])];
    const studentsSheet = XLSX.utils.aoa_to_sheet(studentsData);
    formatSheet(studentsSheet, studentsData);
    XLSX.utils.book_append_sheet(wb, studentsSheet, 'Students');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const tuitionData = [['Tuition Fees'], ['ID', 'Fee', 'Due Date', 'Amount', 'Status'], ...fullData.tuition.map(t => [t.id, t.fee, t.duedate, `₱${Number(t.amount).toLocaleString()}`, t.status])];
    const tuitionSheet = XLSX.utils.aoa_to_sheet(tuitionData);
    formatSheet(tuitionSheet, tuitionData);
    XLSX.utils.book_append_sheet(wb, tuitionSheet, 'Tuition');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const accountsData = [['Accounts'], ['ID', 'Type', 'Username', 'Name'], ...fullData.accounts.map(a => [a.id, a.type, a.username, a.name])];
    const accountsSheet = XLSX.utils.aoa_to_sheet(accountsData);
    formatSheet(accountsSheet, accountsData);
    XLSX.utils.book_append_sheet(wb, accountsSheet, 'Accounts');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const surveyData = [['Survey'], ['ID', 'Name', 'Rating', 'Comment'], ...fullData.survey.map(s => [s.id, s.name, s.rating, s.comment])];
    const surveySheet = XLSX.utils.aoa_to_sheet(surveyData);
    formatSheet(surveySheet, surveyData);
    XLSX.utils.book_append_sheet(wb, surveySheet, 'Survey');
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    XLSX.writeFile(wb, `OASIS_Database_${new Date().toISOString().split('T')[0]}.xlsx`);
}

// Improved AI Chat
function initializeChat() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatbox = document.getElementById('chatbox-modal');
    const chatClose = document.getElementById('chat-close');
    const chatContent = document.querySelector('.chat-content');
    const chatInput = document.getElementById('chat-message');
    const sendButton = document.getElementById('send-message');

    if (!chatToggle || !chatbox || !chatContent || !chatInput || !sendButton) {
        console.error('Chat elements missing');
        showError('Chat interface failed to load.');
        return;
    }

    chatToggle.addEventListener('click', () => chatbox.classList.toggle('hidden'));
    chatClose.addEventListener('click', () => chatbox.classList.add('hidden'));

    let controller = null;
    let isThinking = false;
<<<<<<< HEAD
    const API_KEY = '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'; // Updated API key

    // Enhanced Data Analysis for AI Responses
    function analyzeData(data) {
        const currentDate = new Date();
        const overdueFees = new Set(); // Overdue fees with student and parent details
        const studentBalances = {};
        const parentOverdueAccounts = new Set(); // Unique parent accounts with overdue fees
        let totalOutstandingBalance = 0;
        let totalRevenue = 0;

        // Process tuition and misc fees for overdue and outstanding balances
        [...data.tuition, ...data.misc].forEach(fee => {
            const dueDate = new Date(fee.duedate);
            const amount = parseFloat(fee.amount) || 0;
            const student = data.students.find(s => s.studentnumber === fee.studentnumber);
            const parent = student ? data.accounts.find(a => a.id === student.parent_id && a.type === 'Parent') : null;

            if (fee.status.toLowerCase() === 'unpaid') {
                totalOutstandingBalance += amount;
                studentBalances[fee.studentnumber] = (studentBalances[fee.studentnumber] || 0) + amount;

                if (dueDate < currentDate) {
                    if (student && parent) {
                        overdueFees.add(JSON.stringify({
                            studentnumber: student.studentnumber,
                            firstname: student.firstname,
                            lastname: student.lastname,
                            parent_id: student.parent_id,
                            parent_name: parent.name,
                            fee: fee.fee,
                            duedate: fee.duedate,
                            amount: amount
                        }));
                        parentOverdueAccounts.add(parent.id); // Add unique parent ID
                    }
                }
            }
        });

        // Calculate total revenue from history
        data.history.forEach(entry => {
            totalRevenue += parseFloat(entry.amountpaid) || 0;
        });

        const overdueFeesArray = Array.from(overdueFees).map(f => JSON.parse(f));
        return {
            overdueFees: overdueFeesArray, // Detailed overdue fees
            studentBalances,
            totalOutstandingBalance,
            totalRevenue,
            overdueAccountCount: parentOverdueAccounts.size, // Unique parent accounts with overdue fees
            overdueParents: Array.from(parentOverdueAccounts).map(parentId => {
                const parent = data.accounts.find(a => a.id === parentId);
                return { id: parentId, name: parent.name };
            })
        };
=======
    const API_KEY = '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';

    async function sendMessage(message) {
        appendMessage('user', message);
        showThinking();

        controller = new AbortController();
        try {
            isThinking = true;
            toggleSendIcon();

            const data = await getChartData();
            if (!data) {
                throw new Error('Chart data unavailable');
            }

            const prompt = `
You are an AI assistant for the OASIS admin dashboard. Use the following data from the OASIS database to provide structured and accurate answers:

- Current Date: ${CURRENT_DATE} (use this date for any date-related queries, such as "what is the date today?")
- Accounts (non-sensitive data only): ${JSON.stringify(data.accounts, null, 2)}
- Students: ${JSON.stringify(data.students, null, 2)}
- Tuition Fees: ${JSON.stringify(data.tuition, null, 2)}
- Miscellaneous Fees: ${JSON.stringify(data.misc, null, 2)}
- Payment History: ${JSON.stringify(data.history, null, 2)}
- Surveys: ${JSON.stringify(data.survey, null, 2)}

User question: "${message}"

Instructions:
- For date-related queries (e.g., "what is the date today?"), use the provided Current Date (${CURRENT_DATE}) and respond in the format: "Today’s date is [Month Day, Year]."
- Respond in this exact format when providing an overview or analytics: (use this format only when relevant, omit if not applicable)
  "OASIS Dashboard Overview\\n\\nPayment Distribution (Unpaid Fees):\\n- Tuition: ₱[Amount]\\n- Miscellaneous: ₱[Amount]\\n\\nPayment Trend Over Time:\\n- [Date]: ₱[Amount]\\n- [Date]: ₱[Amount]...\\n\\nKey Dashboard Statistics:\\n- Outstanding Balance: ₱[Amount]\\n- Overdue Accounts: [Number]\\n- Total Students: [Number]\\n- Total Revenue: ₱[Amount]\\n\\nSurvey Insights:\\n- [Suggestion/Advice based on comments]"
- Calculate overdue accounts as the number of tuition and misc records where status = 'unpaid' and duedate < current date (${CURRENT_DATE}).
- Use specific data from the tables when available (e.g., count students, sum amounts, analyze survey comments).
- Format dates as "Month Day, Year" (e.g., "March 13, 2025").
- Use ₱ for currency and commas for numbers (e.g., ₱1,000).
- Do not share sensitive information (e.g., passwords, emails, contact details from accounts table). If asked for such data, respond with: "I cannot provide that information as it is sensitive and violates security protocols."
- For survey comments, analyze the text and ratings to suggest improvements or advice (e.g., if ratings are low with comments about fees, suggest reviewing payment plans).
- Answer the question directly and concisely based on the data.
- If you can't provide a factual answer due to insufficient data, respond with: "I don’t have enough data to answer that accurately."
- Keep responses professional and avoid speculative answers.
            `;

            const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                method: 'POST',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${API_KEY}`
                },
                body: JSON.stringify({
                    model: 'deepseek/deepseek-chat:free',
                    messages: [{ role: 'user', content: prompt }],
                    max_tokens: 500
                })
            });

            if (!response.ok) throw new Error('API request failed');
            const result = await response.json();
            hideThinking();
            appendMessage('ai', result.choices[0]?.message?.content || "Sorry, I couldn’t process that. Try again?");
        } catch (error) {
            hideThinking();
            appendMessage('ai', error.name === 'AbortError' ? 'Message cancelled.' : `Error: ${error.message}`);
            console.error('AI Error:', error);
        } finally {
            isThinking = false;
            toggleSendIcon();
            controller = null;
        }
    }

    function appendMessage(sender, message) {
        const div = document.createElement('div');
        div.className = `${sender}-message p-2 rounded-lg mb-2 max-w-[85%] break-words`;
        div.textContent = message;
        chatContent.appendChild(div);
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    function showThinking() {
        const thinking = document.createElement('div');
        thinking.id = 'thinking';
        thinking.className = 'ai-message p-2 rounded-lg mb-2';
        thinking.textContent = 'Thinking...';
        chatContent.appendChild(thinking);
    }

    function hideThinking() {
        document.getElementById('thinking')?.remove();
    }

    function toggleSendIcon() {
        sendButton.innerHTML = isThinking 
            ? '<span class="material-icons-outlined">stop</span>' 
            : '<span class="material-icons-outlined">send</span>';
    }

    sendButton.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (isThinking && controller) controller.abort();
        else if (message) {
            sendMessage(message);
            chatInput.value = '';
            chatInput.focus();
        }
    });

    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendButton.click();
        }
    });
}

// Data Fetching
async function fetchPaymentData() {
    const fetchWithErrorHandling = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            console.log(`Fetched from ${url}:`, data); // Debugging log
            return data;
        } catch (error) {
            console.error(`Fetch error for ${url}:`, error);
            return [];
        }
    };

    try {
        const [tuitionData, miscData, historyData, studentsData, accountsData, surveyData] = await Promise.all([
            fetchWithErrorHandling(`${apiBaseUrl}fetch_tuition.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_misc.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_history.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_accounts.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_students.php`),
            fetchWithErrorHandling(`${apiBaseUrl}fetch_survey.php`)
        ]);

        return {
            tuition: tuitionData.map(item => ({
                ...item,
                type: 'tuition',
                date: item.duedate
            })),
            misc: miscData.map(item => ({
                ...item,
                type: 'misc',
                date: item.duedate
            })),
            history: historyData.map(item => ({
                ...item,
                type: 'history',
                date: item.transactiondate
            })),
            students: studentsData,
            accounts: accountsData,
            survey: surveyData
        };
    } catch (error) {
        console.error('Fetch error:', error);
        showError('Failed to load payment data.');
        return { tuition: [], misc: [], history: [], students: [], accounts: [], survey: [] };
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    }

    async function sendMessage(message) {
        appendMessage('user', message);
        showThinking();

        controller = new AbortController();
        try {
            isThinking = true;
            toggleSendIcon();

            const data = await getChartData();
            if (!data) throw new Error('Chart data unavailable');

            const analysis = analyzeData(data);
            const currentDate = new Date().toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            const overdueFeesList = analysis.overdueFees.map(f => 
                `- ${f.studentnumber}, ${f.firstname} ${f.lastname}, Parent: ${f.parent_name}, Fee: ${f.fee}, Due: ${f.duedate}, Amount: ₱${f.amount.toLocaleString()} (Overdue)`
            ).join('\n');
            const overdueParentsList = analysis.overdueParents.map(p => `- ${p.name} (ID: ${p.id})`).join('\n');

            const prompt = `
You are OASIS Assistant, an AI chatbot for the Admin dashboard of Oakwood Montessori School’s Online Payment System (OASIS). Your purpose is to assist Admins (e.g., managers of the cashier/treasurer department) with data analytics, decision-making, and reporting based on the OASIS database. Use this data and analysis:

- Current Date: ${currentDate} (use for all date-related queries)
- Accounts (non-sensitive): ${JSON.stringify(data.accounts.map(a => ({ id: a.id, type: a.type, username: a.username, name: a.name })), null, 2)}
- Students: ${JSON.stringify(data.students, null, 2)} (all students are enrolled)
- Tuition Fees: ${JSON.stringify(data.tuition, null, 2)}
- Miscellaneous Fees: ${JSON.stringify(data.misc, null, 2)}
- Payment History: ${JSON.stringify(data.history, null, 2)} (only transactions via OASIS)
- Surveys: ${JSON.stringify(data.survey, null, 2)} (Parent feedback on OASIS)
- Precomputed Analysis:
  - Overdue Fees (as of ${currentDate}): ${overdueFeesList || 'None'}
  - Unique Parent Accounts with Overdue Fees: ${analysis.overdueAccountCount}, List: ${overdueParentsList || 'None'}
  - Student Balances (unpaid tuition + misc): ${JSON.stringify(analysis.studentBalances, null, 2)}
  - Total Outstanding Balance: ₱${analysis.totalOutstandingBalance.toLocaleString()}
  - Total Revenue (via OASIS): ₱${analysis.totalRevenue.toLocaleString()}

User question: "${message}"

Instructions:
- Answer as a professional, friendly assistant for an Admin managing payments.
- For "What’s today’s date?": "Today’s date is ${currentDate}."
- For "How many overdue accounts?" or "How many accounts have overdue fees?": "There are ${analysis.overdueAccountCount} unique parent accounts with overdue fees as of ${currentDate}."
- For "List students with overdue fees" or "Who are the students with overdue fees?": List each as "- [studentnumber], [firstname] [lastname], Parent: [parent_name], Fee: [fee], Due: [duedate], Amount: ₱[amount] (Overdue)".
- For "How many enrolled students?": "There are ${data.students.length} enrolled students as of ${currentDate}."
- For "What is the total revenue?": "Total revenue from OASIS payments is ₱${analysis.totalRevenue.toLocaleString()} (excludes cashier counter payments not recorded in history)."
- For "Balance for student [studentnumber]": "The balance for student [studentnumber] is ₱${(analysis.studentBalances[message.match(/student (\w+)/)?.[1]] || 0).toLocaleString()}."
- For "What fees are overdue?": Summarize overdue fee types (e.g., "X Tuition, Y Miscellaneous") and total amount.
- For survey-related queries (e.g., "What do parents think?"): Summarize ratings and common comments from the survey table.
- Use ₱ and commas for currency (e.g., ₱1,234.56).
- Format lists with each item on a new line (e.g., "- [item]").
- If data is missing: "I don’t have enough data to answer that accurately."
- Avoid sensitive info (e.g., passwords): "I can’t share sensitive details like that."
- Provide concise, actionable insights for decision-making (e.g., "X accounts are overdue, mostly for [fee type], suggesting a pricing review.").
            `;

            const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                method: 'POST',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${API_KEY}`
                },
                body: JSON.stringify({
                    model: 'deepseek/deepseek-chat:free',
                    messages: [{ role: 'user', content: prompt }],
                    max_tokens: 1000 // Increased for more detailed responses
                })
            });

            if (!response.ok) throw new Error('API request failed');
            const result = await response.json();
            hideThinking();
            appendMessage('ai', result.choices[0]?.message?.content || "Sorry, I couldn’t process that. Try again?");
        } catch (error) {
            hideThinking();
            appendMessage('ai', error.name === 'AbortError' ? 'Message cancelled.' : `Error: ${error.message}`);
            console.error('AI Error:', error);
        } finally {
            isThinking = false;
            toggleSendIcon();
            controller = null;
        }
    }

    function appendMessage(sender, message) {
        const div = document.createElement('div');
        div.className = `${sender}-message p-2 rounded-lg mb-2 max-w-[85%] break-words`;
        div.textContent = message;
        chatContent.appendChild(div);
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    function showThinking() {
        const thinking = document.createElement('div');
        thinking.id = 'thinking';
        thinking.className = 'ai-message p-2 rounded-lg mb-2';
        thinking.textContent = 'Thinking...';
        chatContent.appendChild(thinking);
    }

    function hideThinking() {
        document.getElementById('thinking')?.remove();
    }

    function toggleSendIcon() {
        sendButton.innerHTML = isThinking 
            ? '<span class="material-icons-outlined">stop</span>' 
            : '<span class="material-icons-outlined">send</span>';
    }

    sendButton.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (isThinking && controller) controller.abort();
        else if (message) {
            sendMessage(message);
            chatInput.value = '';
            chatInput.focus();
        }
    });

    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendButton.click();
        }
    });
}

<<<<<<< HEAD
// Data Fetching (Updated to use fetch_all_data.php)
async function fetchPaymentData() {
    const fetchWithErrorHandling = async (url) => {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            console.log(`Fetched from ${url}:`, data);
            return data;
        } catch (error) {
            console.error(`Fetch error for ${url}:`, error);
            showError('Failed to load payment data.');
            return {
                accounts: [],
                students: [],
                tuition: [],
                misc: [],
                history: [],
                survey: []
            };
        }
    };

    // Fetch all data from fetch_all_data.php
    const data = await fetchWithErrorHandling(`${apiBaseUrl}fetch_all_data.php`);

    // Add type and date fields for consistency with previous structure
    return {
        tuition: data.tuition.map(item => ({ ...item, type: 'tuition', date: item.duedate })),
        misc: data.misc.map(item => ({ ...item, type: 'misc', date: item.duedate })),
        history: data.history.map(item => ({ ...item, type: 'history', date: item.transactiondate })),
        students: data.students,
        accounts: data.accounts,
        survey: data.survey
    };
}

// Fetch Dashboard Stats (unchanged)
=======
// Fetch Dashboard Stats
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
async function fetchStats() {
    const stats = {
        outstandingBalance: parseFloat(document.getElementById('outstanding-balances')?.textContent.replace('₱', '').replace(',', '')) || 0,
        overdueAccounts: parseInt(document.getElementById('overdue-accounts')?.textContent) || 0,
        totalStudents: parseInt(document.getElementById('total-students')?.textContent) || 0,
        revenue: parseFloat(document.getElementById('revenue')?.textContent.replace('₱', '').replace(',', '')) || 0
    };
    return stats;
}

<<<<<<< HEAD
// Chart Rendering (unchanged)
=======
// Chart Rendering
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
let distributionChart = null;
async function drawCharts() {
    showLoading();
    const data = await fetchPaymentData();
    const stats = await fetchStats();

<<<<<<< HEAD
    const unpaidDistribution = {};
    data.tuition.forEach(entry => {
        if (entry.status.toLowerCase() === 'unpaid') {
=======
    // Unpaid Fees Distribution
    const unpaidDistribution = {};
    data.tuition.forEach(entry => {
        if (entry.status && entry.status.toLowerCase() === 'unpaid') {
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
            unpaidDistribution['Tuition'] = (unpaidDistribution['Tuition'] || 0) + (parseFloat(entry.amount) || 0);
        }
    });
    data.misc.forEach(entry => {
<<<<<<< HEAD
        if (entry.status.toLowerCase() === 'unpaid') {
            unpaidDistribution['Miscellaneous'] = (unpaidDistribution['Miscellaneous'] || 0) + (parseFloat(entry.amount) || 0);
        }
    });
=======
        if (entry.status && entry.status.toLowerCase() === 'unpaid') {
            unpaidDistribution['Miscellaneous'] = (unpaidDistribution['Miscellaneous'] || 0) + (parseFloat(entry.amount) || 0);
        }
    });
    console.log('Unpaid Distribution:', unpaidDistribution); // Debugging log
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e

    const ctx1 = document.getElementById('paymentDistributionChart');
    const distributionNoData = document.getElementById('distribution-no-data');
    const togglePie = document.getElementById('toggle-pie');
    const toggleBar = document.getElementById('toggle-bar');

<<<<<<< HEAD
=======
    // Check if there's data for the distribution chart
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const hasDistributionData = Object.keys(unpaidDistribution).length > 0 && Object.values(unpaidDistribution).some(val => val > 0);

    if (!hasDistributionData) {
        ctx1.style.display = 'none';
        distributionNoData.style.display = 'block';
        togglePie.style.display = 'none';
        toggleBar.style.display = 'none';
    } else {
        ctx1.style.display = 'block';
        distributionNoData.style.display = 'none';
        togglePie.style.display = 'inline-block';
        toggleBar.style.display = 'inline-block';

        function renderChart(type) {
            if (distributionChart) distributionChart.destroy();
<<<<<<< HEAD
=======
            
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
            const commonOptions = {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 20 } },
                    title: { display: true, text: 'Unpaid Fees Distribution', font: { size: 18, weight: 'bold', family: 'Montserrat' }, color: '#333', padding: 20 },
                    datalabels: { color: type === 'pie' ? '#fff' : '#333', font: { weight: 'bold', size: 12, family: 'Montserrat' }, formatter: value => `₱${value.toLocaleString()}`, anchor: 'end', align: 'end' },
                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', titleFont: { family: 'Montserrat' }, bodyFont: { family: 'Montserrat' }, callbacks: { label: context => `${context.label}: ₱${context.raw.toLocaleString()}` } }
                },
                animation: { duration: 1500, easing: 'easeInOutCubic' }
            };

            distributionChart = new Chart(ctx1, {
                type: type === 'pie' ? 'pie' : 'bar',
                data: {
                    labels: Object.keys(unpaidDistribution),
                    datasets: [{
                        data: Object.values(unpaidDistribution),
                        backgroundColor: type === 'pie' ? ['#00C4CC', '#FF6B6B'] : '#00C4CC',
                        borderColor: type === 'pie' ? '#ffffff' : '#00C4CC',
                        borderWidth: 2,
                        hoverBorderWidth: 3,
                        hoverOffset: type === 'pie' ? 10 : 0
                    }]
                },
<<<<<<< HEAD
                options: type === 'pie' ? { ...commonOptions, cutout: '70%' } : {
                    ...commonOptions,
                    scales: { y: { beginAtZero: true, ticks: { callback: v => `₱${v.toLocaleString()}` } } }
=======
                options: type === 'pie' ? {
                    ...commonOptions,
                    cutout: '70%'
                } : {
                    ...commonOptions,
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => `₱${v.toLocaleString()}` } }
                    }
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
                },
                plugins: [ChartDataLabels]
            });
        }

        renderChart('pie');
<<<<<<< HEAD
        togglePie.addEventListener('click', () => { togglePie.classList.add('active'); toggleBar.classList.remove('active'); renderChart('pie'); });
        toggleBar.addEventListener('click', () => { toggleBar.classList.add('active'); togglePie.classList.remove('active'); renderChart('bar'); });
    }

    const trendData = {};
    data.history.forEach(entry => {
        if (entry.transactiondate && entry.amountpaid > 0) {
=======
        togglePie.addEventListener('click', () => {
            togglePie.classList.add('active');
            toggleBar.classList.remove('active');
            renderChart('pie');
        });
        toggleBar.addEventListener('click', () => {
            toggleBar.classList.add('active');
            togglePie.classList.remove('active');
            renderChart('bar');
        });
    }

    // Payment Trend (using history data only)
    const trendData = {};
    data.history.forEach(entry => {
        if (entry.transactiondate && entry.amountpaid > 0) {
            // Parse the full timestamp and extract date only, handling timezone
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
            const date = new Date(entry.transactiondate.split(' ')[0]).toISOString().split('T')[0];
            trendData[date] = (trendData[date] || 0) + parseFloat(entry.amountpaid);
        }
    });
<<<<<<< HEAD
=======
    console.log('Trend Data:', trendData); // Debugging log
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e

    const dates = Object.keys(trendData).sort();
    const amounts = dates.map(d => trendData[d]);
    const ctx2 = document.getElementById('paymentTrendChart');
    const trendNoData = document.getElementById('trend-no-data');

<<<<<<< HEAD
=======
    // Check if there's data for the trend chart
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
    const hasTrendData = dates.length > 0 && amounts.some(val => val > 0);

    if (!hasTrendData) {
        ctx2.style.display = 'none';
        trendNoData.style.display = 'block';
    } else {
        ctx2.style.display = 'block';
        trendNoData.style.display = 'none';
<<<<<<< HEAD
=======

>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Payment Trend (PHP)',
                    data: amounts,
                    borderColor: '#00C4CC',
                    backgroundColor: context => {
                        const ctx = context.chart.ctx;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(0, 196, 204, 0.3)');
                        gradient.addColorStop(1, 'rgba(0, 196, 204, 0)');
                        return gradient;
                    },
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#00C4CC',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 20 } },
                    title: { display: true, text: 'Payment Trends Over Time', font: { size: 18, weight: 'bold', family: 'Montserrat' }, color: '#333', padding: 20 },
                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', titleFont: { family: 'Montserrat' }, bodyFont: { family: 'Montserrat' }, callbacks: { label: context => `₱${context.raw.toLocaleString()}` } }
                },
                scales: {
<<<<<<< HEAD
                    x: { type: 'time', time: { unit: 'day' }, title: { display: true, text: 'Date', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, grid: { display: false }, ticks: { color: '#666' } },
                    y: { beginAtZero: true, title: { display: true, text: 'Amount (PHP)', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, grid: { color: 'rgba(0, 0, 0, 0.05)' }, ticks: { color: '#666', callback: v => `₱${v.toLocaleString()}` } }
=======
                    x: { 
                        type: 'time', 
                        time: { unit: 'day' }, 
                        title: { display: true, text: 'Date', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, 
                        grid: { display: false }, 
                        ticks: { color: '#666' } 
                    },
                    y: { 
                        beginAtZero: true, 
                        title: { display: true, text: 'Amount (PHP)', font: { size: 14, family: 'Montserrat' }, color: '#333', padding: 10 }, 
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }, 
                        ticks: { color: '#666', callback: v => `₱${v.toLocaleString()}` } 
                    }
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
                },
                animation: { duration: 1500, easing: 'easeInOutCubic' }
            }
        });
    }

    hideLoading();
    return {
        distributionData: unpaidDistribution,
        trendData: { dates, amounts },
        stats: stats,
        history: data.history,
        misc: data.misc,
        students: data.students,
        tuition: data.tuition,
        accounts: data.accounts,
        survey: data.survey
    };
}

// Initialization
let chartData = null;
async function getChartData() {
    if (!chartData) chartData = await drawCharts();
    return chartData;
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
<<<<<<< HEAD
=======
        // Ensure Chart is globally available and register plugins
>>>>>>> 92d29db263371feb3a8842432bf750b9a4a69a2e
        if (typeof window !== 'undefined' && typeof Chart !== 'undefined') {
            window.Chart = Chart;
            Chart.register(ChartDataLabels);
        } else {
            throw new Error('Chart.js is not loaded properly');
        }

        chartData = await drawCharts();
        initializeChat();

        document.getElementById('export-excel').addEventListener('click', () => {
            exportToExcel(chartData);
        });
    } catch (error) {
        console.error('Initialization error:', error);
        showError('Failed to initialize dashboard.');
    }
});