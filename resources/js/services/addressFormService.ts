import { ref, Ref } from "vue"
import axios from "axios"
import { route } from "ziggy-js"
import type { Address } from "@/types/Address"

export interface Owner {
    ownerId: string | number
    ownerType: string
}

/**
 * Create a new address for a polymorphic owner
 */
export async function createAddress(data: Partial<Address>, owner: Owner): Promise<Address> {
    const payload = {
        ...data,
        owner_type: owner.ownerType,
        owner_id: owner.ownerId,
    }

    const response = await axios.post(route("addresses.store"), payload, {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    })

    return response.data.address ?? response.data
}

/**
 * Update an existing address
 */
export async function updateAddress(id: string | number, data: Partial<Address>, owner: Owner): Promise<Address> {
    const payload = {
        ...data,
        owner_type: owner.ownerType,
        owner_id: owner.ownerId,
    }

    const response = await axios.patch(route("addresses.update", { address: id }), payload, {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    })

    return response.data.address ?? response.data
}

/**
 * Delete an address
 */
export async function deleteAddress(id: string | number): Promise<void> {
    await axios.delete(route("addresses.destroy", { address: id }), {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    })
}

