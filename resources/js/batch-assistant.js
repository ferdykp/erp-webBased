export default function batchAssistant(remaining) {
            return {
                remaining: Number(remaining) || 0,
                limit: 0,
                quantities: [],
                verified: false,

                get allocated() {
                    return this.quantities.reduce(
                        (sum, quantity) => sum + (Number(quantity) || 0),
                        0
                    );
                },

                get ready() {
                    return (
                        this.verified &&
                        this.quantities.length > 0 &&
                        this.quantities.every(
                            quantity =>
                            Number.isInteger(Number(quantity)) &&
                            Number(quantity) > 0
                        ) &&
                        this.allocated === this.remaining
                    );
                },

                // Shortcut: allocate everything to one batch.
                useSingleBatch() {
                    if (
                        !Number.isSafeInteger(this.remaining) ||
                        this.remaining <= 0
                    ) {
                        return;
                    }

                    this.limit = this.remaining;
                    this.quantities = [this.remaining];

                    // Require verification again after changing allocation.
                    this.verified = false;
                },

                // Existing quantity-based batch planning.
                generate() {
                    this.verified = false;
                    this.quantities = [];

                    const limit = Number(this.limit);

                    if (
                        !Number.isSafeInteger(limit) ||
                        limit <= 0 ||
                        this.remaining <= 0
                    ) {
                        return;
                    }

                    let left = this.remaining;

                    while (left > 0 && this.quantities.length < 1000) {
                        const quantity = Math.min(left, limit);

                        this.quantities.push(quantity);

                        left -= quantity;
                    }
                },
            };
        }
