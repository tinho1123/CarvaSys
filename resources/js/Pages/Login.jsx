import { useForm } from '@inertiajs/react';

export default function Login() {
    const { clearErrors, setData, data } = useForm();
    return <h1 className="text-3xl font-bold underline bg-red-900">Hello world!</h1>;
}
